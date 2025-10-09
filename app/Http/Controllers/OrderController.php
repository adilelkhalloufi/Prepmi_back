<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // list order for this user
        $orders = Auth::user()->orders(
        )->with('product')
            ->get();

        return response()->json(OrderResource::collection($orders));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // create new order
        // test validate should have params call products and it array and product should have id
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'note' => 'nullable|string',
            'payment' => 'required|in:1,2,3,4',
        ]);
        // boucle this product to create order for each product
        foreach ($request->products as $product) {
            $order =
             Auth::user()
                 ->orders()
                 ->create([
                     'product_id' => $product['id'],
                     'quantity' => $product['quantity'] ?? 1,
                      'note' => $request->note,
                     'payment' => $request->payment,

                 ]);
        }

        return response()->json($order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        // delete order
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }

    public function GetOrderForSeller(): JsonResponse
    {
        $orders = Order::whereHas('product', function ($query): void {
            $query->where('user_id', Auth::id());
        })
            ->with(['product' => function ($query): void {
                $query->where('user_id', Auth::id());
            }])
            ->get();

        return response()->json(OrderResource::collection($orders));
    }
}
