<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderMealsNotification;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order)
    {
        // Send meal details to the client (user)
        if ($order->user && $order->meals && $order->meals->count() > 0) {
            $order->user->notify(new OrderMealsNotification($order));
        }
    }
}
