<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'amount' => $this->amount,
            'transaction_id' => $this->transaction_id,
            'paid_at' => $this->paid_at?->toDateTimeString(),

            // Optional: include order info if eager loaded
            'order' => $this->whenLoaded('order', function () {
                return [
                    'id' => $this->order->id,
                    'invoice_no' => $this->order->invoice_no,
                    'total_price' => $this->order->total_price,
                    'status' => $this->order->status,
                ];
            }),

            // Optional: include creator info if loaded
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),

            'updater' => $this->whenLoaded('updater', function () {
                return $this->updater
                    ? [
                        'id' => $this->updater->id,
                        'name' => $this->updater->name,
                        'email' => $this->updater->email,
                    ]
                    : null;
            }),
        ];
    }
}
