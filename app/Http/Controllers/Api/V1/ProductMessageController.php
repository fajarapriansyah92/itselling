<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\AdminNotification;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\ProductInquiryMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductMessageController extends Controller
{
    /**
     * Tampilkan riwayat pesan konsultasi terkait produk ini.
     */
    public function index(Request $request, Product $product): JsonResponse
    {
        $inquiry = ProductInquiry::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        // Tandai pesan dari admin sebagai telah dibaca oleh pelanggan
        $inquiry->messages()
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $inquiry->messages()->with('user')->orderBy('created_at')->get();

        return response()->json([
            'inquiry_id' => $inquiry->id,
            'product' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'unit' => $product->unit,
                'image_url' => $product->image_url,
                'stock' => $product->stock,
                'type' => $product->type,
                'type_label' => $product->type_label,
            ],
            'data' => $messages->map(fn ($msg) => [
                'id' => $msg->id,
                'order_id' => null,
                'user_id' => $msg->user_id,
                'sender_type' => $msg->sender_type,
                'is_me' => $msg->user_id === $request->user()->id && $msg->sender_type === 'customer',
                'sender_name' => $msg->sender_type === 'admin' ? 'Customer Support DSC' : ($msg->user?->name ?? 'Anda'),
                'message' => $msg->message,
                'read_at' => $msg->read_at?->toIso8601String(),
                'created_at' => $msg->created_at->toIso8601String(),
                'created_at_human' => $msg->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Kirim pertanyaan baru tentang produk dari pelanggan ke admin.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $inquiry = ProductInquiry::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        $message = $inquiry->messages()->create([
            'user_id' => $request->user()->id,
            'sender_type' => 'customer',
            'message' => $request->message,
        ]);

        // Buat notifikasi agar admin langsung auto-detect pertanyaan produk ini
        AdminNotification::create([
            'type' => 'order_chat',
            'title' => "Tanya Produk: {$product->name}",
            'message' => "{$request->user()->name}: \"{$message->message}\"",
            'link' => route('admin.chats.product.show', $inquiry),
        ]);

        return response()->json([
            'data' => [
                'id' => $message->id,
                'order_id' => null,
                'user_id' => $message->user_id,
                'sender_type' => $message->sender_type,
                'is_me' => true,
                'sender_name' => $request->user()->name,
                'message' => $message->message,
                'read_at' => null,
                'created_at' => $message->created_at->toIso8601String(),
                'created_at_human' => $message->created_at->diffForHumans(),
            ],
        ], 201);
    }

    /**
     * Tampilkan semua daftar produk yang pernah ditanyakan oleh pelanggan yang sedang login.
     */
    public function userInquiries(Request $request): JsonResponse
    {
        $inquiries = ProductInquiry::query()
            ->where('user_id', $request->user()->id)
            ->whereHas('messages')
            ->with(['product.category', 'latestMessage'])
            ->withCount(['messages as unread_messages_count' => fn ($q) => $q->where('sender_type', 'admin')->whereNull('read_at')])
            ->orderByDesc(
                ProductInquiryMessage::select('id')
                    ->whereColumn('product_inquiry_id', 'product_inquiries.id')
                    ->latest('id')
                    ->limit(1)
            )
            ->get();

        $totalUnread = ProductInquiryMessage::query()
            ->whereHas('inquiry', fn ($q) => $q->where('user_id', $request->user()->id))
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'total_unread' => $totalUnread,
            'data' => $inquiries->map(function ($inq) {
                $latest = $inq->latestMessage;
                $product = $inq->product;

                return [
                    'id' => $inq->id,
                    'unread_count' => $inq->unread_messages_count,
                    'product' => (new ProductResource($product))->resolve(),
                    'latest_message' => $latest ? [
                        'message' => $latest->message,
                        'sender_type' => $latest->sender_type,
                        'is_me' => $latest->sender_type === 'customer',
                        'time_human' => $latest->created_at->diffForHumans(),
                        'created_at' => $latest->created_at->toIso8601String(),
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Hitung total pesan yang belum dibaca dari admin untuk badge header APK.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $totalUnread = ProductInquiryMessage::query()
            ->whereHas('inquiry', fn ($q) => $q->where('user_id', $request->user()->id))
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $totalUnread,
        ]);
    }
}
