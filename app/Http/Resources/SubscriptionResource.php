<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
            'order_id' => $this->order_id,
            'status' => $this->status,
            'started_at' => $this->started_at ? (is_string($this->started_at) ? $this->started_at : $this->started_at->format('Y-m-d H:i:s')) : null,
            'ends_at' => $this->ends_at ? (is_string($this->ends_at) ? $this->ends_at : $this->ends_at->format('Y-m-d H:i:s')) : null,
            'next_billing_date' => $this->next_billing_date ? (is_string($this->next_billing_date) ? $this->next_billing_date : $this->next_billing_date->format('Y-m-d')) : null,
            'next_delivery_date' => $this->next_delivery_date ? (is_string($this->next_delivery_date) ? $this->next_delivery_date : $this->next_delivery_date->format('Y-m-d')) : null,
            'cancellation_deadline' => $this->cancellation_deadline ? (is_string($this->cancellation_deadline) ? $this->cancellation_deadline : $this->cancellation_deadline->format('Y-m-d')) : null,
            'paused_at' => $this->paused_at ? (is_string($this->paused_at) ? $this->paused_at : $this->paused_at->format('Y-m-d H:i:s')) : null,
            'pause_reason' => $this->pause_reason,
            'pause_start_date' => $this->pause_start_date ? (is_string($this->pause_start_date) ? $this->pause_start_date : $this->pause_start_date->format('Y-m-d')) : null,
            'pause_end_date' => $this->pause_end_date ? (is_string($this->pause_end_date) ? $this->pause_end_date : $this->pause_end_date->format('Y-m-d')) : null,
            'max_pause_weeks' => $this->max_pause_weeks,
            'paused_weeks_used' => $this->paused_weeks_used,
            'preferred_delivery_days' => $this->preferred_delivery_days ? json_decode($this->preferred_delivery_days) : null,
            'delivery_restrictions' => $this->delivery_restrictions,
            'auto_renew' => $this->auto_renew,
            'auto_renew_disabled_at' => $this->auto_renew_disabled_at ? (is_string($this->auto_renew_disabled_at) ? $this->auto_renew_disabled_at : $this->auto_renew_disabled_at->format('Y-m-d H:i:s')) : null,
            'cancelled_at' => $this->cancelled_at ? (is_string($this->cancelled_at) ? $this->cancelled_at : $this->cancelled_at->format('Y-m-d H:i:s')) : null,
            'cancellation_reason' => $this->cancellation_reason,
            'weeks_committed' => $this->weeks_committed,
            'weeks_remaining' => $this->weeks_remaining,
            'total_amount_paid' => $this->total_amount_paid,
            'meals_delivered' => $this->meals_delivered,
            'delivery_address' => $this->delivery_address,
            'delivery_notes' => $this->delivery_notes,
            'special_instructions' => $this->special_instructions,
            'created_at' => $this->created_at ? (is_string($this->created_at) ? $this->created_at : $this->created_at->format('Y-m-d H:i:s')) : null,
            'updated_at' => $this->updated_at ? (is_string($this->updated_at) ? $this->updated_at : $this->updated_at->format('Y-m-d H:i:s')) : null,

            // Related data (loaded if available)
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'email' => $this->user->email,
                ];
            }),

            'plan' => $this->whenLoaded('plan', function () {
                return [
                    'id' => $this->plan->id,
                    'name' => $this->plan->name,
                    'meals_per_week' => $this->plan->meals_per_week,
                    'price_per_week' => $this->plan->price_per_week,
                    'price_subscription_per_week' => $this->plan->price_subscription_per_week,
                ];
            }),

            'order' => $this->whenLoaded('order', function () {
                return [
                    'id' => $this->order->id,
                    'num_order' => $this->order->num_order,
                    'total_amount' => $this->order->total_amount,
                    'statue' => $this->order->statue,
                ];
            }),
        ];
    }
}
