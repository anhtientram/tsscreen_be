<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Packet;
use App\Models\Transaction;

class OrderActivationService
{
    public function activate(
        Order $order,
        string $validDate,
        ?string $paymentDate = null,
        ?string $packetId = null,
    ): Order {
        $validDate = substr((string) $validDate, 0, 10);
        $paymentDate = substr((string) ($paymentDate ?? now()->format('Y-m-d')), 0, 10);

        if ($packetId) {
            $order->packet_id = $packetId;
            $packet = Packet::query()->where('packet_id', $packetId)->first();
            if ($packet) {
                $order->setRelation('packet', $packet);
            }
        }

        $order->pay = '1';
        $order->valid_date = $validDate;
        $order->payment_date = $paymentDate;
        $order->expire_date = substr($order->computeExpireDate($validDate), 0, 10);
        $order->deleted = 'n';
        $order->save();

        Transaction::query()->create([
            'paid_id' => $order->paid_id,
            'packet_id' => $order->packet_id,
            'customer_id' => $order->customer_id,
            'reg_number' => $order->reg_number,
            'name_packet' => $order->name_packet,
            'amount' => $order->price,
            'payment_date' => $paymentDate,
            'ref_transaction_id' => '',
            'created_date' => now(),
        ]);

        return $order;
    }
}
