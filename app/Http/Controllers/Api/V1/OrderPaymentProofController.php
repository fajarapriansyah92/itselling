<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePaymentProofRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class OrderPaymentProofController extends Controller
{
    /**
     * Unggah bukti transfer. Admin akan memverifikasi lalu mengubah status menjadi "Pembayaran Diterima".
     */
    public function store(StorePaymentProofRequest $request, Order $order): OrderResource
    {
        $previousPath = $order->payment_proof_path;

        $order->update([
            'payment_proof_path' => $request->file('payment_proof')->store('payment-proofs', 'public'),
        ]);

        if ($previousPath !== null) {
            Storage::disk('public')->delete($previousPath);
        }

        return (new OrderResource($order->load('items')))
            ->additional(['message' => 'Bukti pembayaran berhasil diunggah.']);
    }
}
