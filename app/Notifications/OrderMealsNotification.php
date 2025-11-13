<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Order;

class OrderMealsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $meals = $this->order->meals;
        $mealList = $meals->map(function($meal) {
            return $meal->name;
        })->implode(', ');

        return (new MailMessage)
            ->subject('Your Order Meals')
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('Thank you for your order!')
            ->line('Here are the meals included in your order:')
            ->line($mealList)
            ->line('We hope you enjoy your meals!');
    }
}
