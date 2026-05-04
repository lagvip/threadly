<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing([
            'details',
            'details.variant' => function ($query) {
                $query->withTrashed();
            },
            'details.variant.color' => function ($query) {
                $query->withTrashed();
            },
            'details.variant.size' => function ($query) {
                $query->withTrashed();
            },
        ]);
    }

    public function build()
    {
        $subject = 'Xác nhận đơn hàng #' . $this->order->order_code;

        if ($this->order->payment_method === 'vnpay' && $this->order->payment_status === 'paid') {
            $subject = 'Thanh toán thành công - Đơn hàng #' . $this->order->order_code;
        }

        return $this->subject($subject)
            ->view('emails.order-placed');
    }
}
