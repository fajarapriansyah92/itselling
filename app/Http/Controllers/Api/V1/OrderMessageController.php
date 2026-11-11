<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderMessageResource;
use App\Models\AdminNotification;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class OrderMessageController extends Controller
{
    /**
     * Ambil riwayat chat pesan untuk pesanan tertentu.
     */
    public function index(Request $request, Order $order): AnonymousResourceCollection
    {
        abort_if($order->user_id !== $request->user()->id, 403, 'Akses pesanan ditolak.');

        // Tandai pesan dari admin sebagai telah dibaca oleh customer
        $order->messages()
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $order->messages()
            ->with('user')
            ->oldest()
            ->get();

        return OrderMessageResource::collection($messages);
    }

    /**
     * Kirim pesan baru dari customer ke admin untuk pesanan ini.
     */
    public function store(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403, 'Akses pesanan ditolak.');

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = $order->messages()->create([
            'user_id' => $request->user()->id,
            'sender_type' => 'customer',
            'message' => trim($validated['message']),
        ]);

        // Buat notifikasi untuk Admin Dashboard
        AdminNotification::create([
            'type' => 'order_chat',
            'title' => 'Pesan Baru Pesanan #'.$order->order_number,
            'message' => $request->user()->name.': '.Str::limit($message->message, 80),
            'link' => route('admin.orders.show', $order),
        ]);

        return (new OrderMessageResource($message->load('user')))
            ->response()
            ->setStatusCode(201);
    }
}
