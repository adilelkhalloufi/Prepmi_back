<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\MembershipTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MembershipTransactionController extends Controller
{
    /**
     * Display a listing of membership transactions.
     */
    public function index(Request $request)
    {
        $query = MembershipTransaction::with(['membership.membershipPlan', 'user']);

        // Filter by membership
        if ($request->has('membership_id')) {
            $query->where('membership_id', $request->membership_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by transaction type
        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('billing_period_start', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('billing_period_end', '<=', $request->to_date);
        }

        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $perPage = $request->get('per_page', 15);
        
        if ($request->boolean('paginate', true)) {
            $transactions = $query->paginate($perPage);
        } else {
            $transactions = $query->get();
        }

        return response()->json($transactions);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'membership_id' => 'required|exists:memberships,id',
            'amount' => 'required|numeric|min:0',
            'transaction_type' => 'required|string|in:monthly_charge,refund,adjustment',
            'payment_method' => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'billing_period_start' => 'required|date',
            'billing_period_end' => 'required|date|after:billing_period_start',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $membership = Membership::find($request->membership_id);

        DB::beginTransaction();
        try {
            $transaction = MembershipTransaction::create([
                'membership_id' => $request->membership_id,
                'user_id' => $membership->user_id,
                'amount' => $request->amount,
                'transaction_type' => $request->transaction_type,
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'billing_period_start' => $request->billing_period_start,
                'billing_period_end' => $request->billing_period_end,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Transaction created successfully',
                'data' => $transaction->load(['membership', 'user'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show($id)
    {
        $transaction = MembershipTransaction::with([
            'membership.membershipPlan',
            'user'
        ])->find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json($transaction);
    }

    /**
     * Mark transaction as completed.
     */
    public function markCompleted(Request $request, $id)
    {
        $transaction = MembershipTransaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        if ($transaction->isCompleted()) {
            return response()->json([
                'message' => 'Transaction is already completed'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'payment_reference' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $transaction->update([
                'payment_status' => 'completed',
                'charged_at' => now(),
                'payment_reference' => $request->payment_reference ?? $transaction->payment_reference,
            ]);

            // Update membership next billing date if this is a monthly charge
            if ($transaction->transaction_type === 'monthly_charge') {
                $membership = $transaction->membership;
                $nextBillingDate = Carbon::parse($transaction->billing_period_end)->addDay();
                $membership->update([
                    'next_billing_date' => $nextBillingDate,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaction marked as completed',
                'data' => $transaction->fresh(['membership', 'user'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark transaction as failed.
     */
    public function markFailed(Request $request, $id)
    {
        $transaction = MembershipTransaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        if ($transaction->isFailed()) {
            return response()->json([
                'message' => 'Transaction is already marked as failed'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'failure_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $transaction->update([
            'payment_status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $request->failure_reason,
        ]);

        return response()->json([
            'message' => 'Transaction marked as failed',
            'data' => $transaction->fresh(['membership', 'user'])
        ]);
    }

    /**
     * Process refund for a transaction.
     */
    public function refund(Request $request, $id)
    {
        $transaction = MembershipTransaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        if (!$transaction->isCompleted()) {
            return response()->json([
                'message' => 'Only completed transactions can be refunded'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'refund_amount' => 'nullable|numeric|min:0|max:' . $transaction->amount,
            'refund_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $refundAmount = $request->refund_amount ?? $transaction->amount;

        DB::beginTransaction();
        try {
            // Update original transaction
            $transaction->update([
                'payment_status' => 'refunded',
            ]);

            // Create refund transaction
            $refundTransaction = MembershipTransaction::create([
                'membership_id' => $transaction->membership_id,
                'user_id' => $transaction->user_id,
                'amount' => -$refundAmount,
                'transaction_type' => 'refund',
                'payment_status' => 'completed',
                'billing_period_start' => $transaction->billing_period_start,
                'billing_period_end' => $transaction->billing_period_end,
                'charged_at' => now(),
                'notes' => 'Refund for transaction #' . $transaction->id . '. Reason: ' . $request->refund_reason,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Refund processed successfully',
                'data' => [
                    'original_transaction' => $transaction->fresh(['membership', 'user']),
                    'refund_transaction' => $refundTransaction->load(['membership', 'user'])
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process refund',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transactions for a specific membership.
     */
    public function getByMembership($membershipId)
    {
        $membership = Membership::find($membershipId);

        if (!$membership) {
            return response()->json([
                'message' => 'Membership not found'
            ], 404);
        }

        $transactions = MembershipTransaction::where('membership_id', $membershipId)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($transactions);
    }

    /**
     * Get transactions for a specific user.
     */
    public function getByUser($userId)
    {
        $transactions = MembershipTransaction::where('user_id', $userId)
            ->with(['membership.membershipPlan'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($transactions);
    }

    /**
     * Get transaction statistics.
     */
    public function statistics(Request $request)
    {
        $query = MembershipTransaction::query();

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->where('billing_period_start', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('billing_period_end', '<=', $request->to_date);
        }

        $stats = [
            'total_transactions' => $query->count(),
            'completed_transactions' => (clone $query)->where('payment_status', 'completed')->count(),
            'pending_transactions' => (clone $query)->where('payment_status', 'pending')->count(),
            'failed_transactions' => (clone $query)->where('payment_status', 'failed')->count(),
            'total_revenue' => (clone $query)->where('payment_status', 'completed')
                ->where('transaction_type', 'monthly_charge')
                ->sum('amount'),
            'total_refunds' => (clone $query)->where('payment_status', 'refunded')
                ->sum('amount'),
            'revenue_by_month' => (clone $query)->where('payment_status', 'completed')
                ->where('transaction_type', 'monthly_charge')
                ->select(
                    DB::raw('YEAR(billing_period_start) as year'),
                    DB::raw('MONTH(billing_period_start) as month'),
                    DB::raw('SUM(amount) as total')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ];

        return response()->json($stats);
    }
}
