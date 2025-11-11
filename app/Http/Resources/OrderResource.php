<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\PaymentResource;

class OrderResource extends JsonResource
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
            'invoice_no' => $this->invoice_no,
            'order_date' => $this->order_date?->toDateTimeString(),
            
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name ?? null,
                    'email' => $this->customer->email ?? null,
                    'phone' => $this->customer->phone ?? null,
                ];
            }),

            'total_price' => $this->total_price,
            'total_items' => $this->total_items,
            'total_qty' => $this->total_qty,
            'status' => $this->status,
            'note' => $this->note,
            'is_inside_dhaka' => $this->is_inside_dhaka,
            'shipping_cost' => $this->shipping_cost,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'address' => $this->address,

            'updater' => $this->whenLoaded('updater', function () {
                return [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                    'email' => $this->updater->email,
                ];
            }),

            // Nested relations
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
