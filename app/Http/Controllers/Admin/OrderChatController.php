<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\ProductInquiry;
use App\Models\ProductInquiryMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderChatController extends Controller
{
    /**
     * Tampilkan inbox semua percakapan pesanan dan konsultasi produk dari pelanggan.
     */
    public function index(Request $request): View|JsonResponse
    {
        $activeTab = $request->query('tab', 'all');

        $ordersWithChats = Order::query()
            ->whereHas('messages')
            ->with(['user', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount(['messages as unread_messages_count' => fn ($q) => $q->where('sender_type', 'customer')->whereNull('read_at')])
            ->orderByDesc(
                OrderMessage::select('created_at')
                    ->whereColumn('order_id', 'orders.id')
                    ->latest()
                    ->limit(1)
            )
            ->paginate(20, ['*'], 'order_page');

        $productInquiries = ProductInquiry::query()
            ->whereHas('messages')
            ->with(['user', 'product.category', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount(['messages as unread_messages_count' => fn ($q) => $q->where('sender_type', 'customer')->whereNull('read_at')])
            ->orderByDesc(
                ProductInquiryMessage::select('created_at')
                    ->whereColumn('product_inquiry_id', 'product_inquiries.id')
                    ->latest()
                    ->limit(1)
            )
            ->paginate(20, ['*'], 'inquiry_page');

        $totalUnreadOrders = OrderMessage::where('sender_type', 'customer')->whereNull('read_at')->count();
        $totalUnreadProducts = ProductInquiryMessage::where('sender_type', 'customer')->whereNull('read_at')->count();

        if ($request->wantsJson()) {
            return response()->json([
                'total_unread' => $totalUnreadOrders + $totalUnreadProducts,
                'total_unread_products' => $totalUnreadProducts,
                'total_unread_orders' => $totalUnreadOrders,
                'product_inquiries_total' => $productInquiries->total(),
                'orders_total' => $ordersWithChats->total(),
                'product_inquiries' => collect($productInquiries->items())->map(function ($inquiry) {
                    $latestMsg = $inquiry->messages->first();

                    return [
                        'id' => $inquiry->id,
                        'user_name' => $inquiry->user->name,
                        'user_initial' => strtoupper(substr($inquiry->user->name, 0, 1)),
                        'product_name' => $inquiry->product->name,
                        'product_sku' => $inquiry->product->sku,
                        'unread_count' => $inquiry->unread_messages_count,
                        'latest_message' => $latestMsg ? $latestMsg->message : null,
                        'latest_sender_type' => $latestMsg ? $latestMsg->sender_type : null,
                        'time_human' => $latestMsg ? $latestMsg->created_at->diffForHumans() : $inquiry->created_at->diffForHumans(),
                        'chat_url' => route('admin.chats.product.show', $inquiry),
                    ];
                }),
                'orders' => collect($ordersWithChats->items())->map(function ($order) {
                    $latestMsg = $order->messages->first();

                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'user_name' => $order->user->name,
                        'user_initial' => strtoupper(substr($order->user->name, 0, 1)),
                        'unread_count' => $order->unread_messages_count,
                        'latest_message' => $latestMsg ? $latestMsg->message : null,
                        'latest_sender_type' => $latestMsg ? $latestMsg->sender_type : null,
                        'time_human' => $latestMsg ? $latestMsg->created_at->diffForHumans() : $order->created_at->diffForHumans(),
                        'chat_url' => route('admin.orders.show', $order).'#chat-section',
                    ];
                }),
            ]);
        }

        return view('admin.chats.index', compact(
            'ordersWithChats',
            'productInquiries',
            'activeTab',
            'totalUnreadOrders',
            'totalUnreadProducts'
        ));
    }

    /**
     * Buka ruang chat percakapan konsultasi produk spesifik.
     */
    public function showProduct(ProductInquiry $inquiry, Request $request): View|JsonResponse
    {
        // Tandai pesan customer sebagai telah dibaca oleh admin
        $inquiry->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            $inquiry->load(['messages.user']);

            return response()->json([
                'messages' => $inquiry->messages->map(fn ($msg) => [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'sender_name' => $msg->sender_type === 'admin' ? 'Anda (Admin)' : ($msg->user?->name ?? 'Pelanggan'),
                    'message' => $msg->message,
                    'time' => $msg->created_at->format('H:i'),
                    'read_at' => (bool) $msg->read_at,
                ]),
            ]);
        }

        $inquiry->load(['user', 'product.category', 'messages.user']);

        return view('admin.chats.product-show', compact('inquiry'));
    }

    /**
     * Admin mengirim balasan ke pelanggan terkait konsultasi produk ini.
     */
    public function replyProduct(Request $request, ProductInquiry $inquiry): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = $inquiry->messages()->create([
            'user_id' => $request->user()->id,
            'sender_type' => 'admin',
            'message' => trim($validated['message']),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'sender_type' => 'admin',
                    'sender_name' => 'Anda (Admin)',
                    'message' => $message->message,
                    'time' => $message->created_at->format('H:i'),
                    'read_at' => false,
                ],
            ]);
        }

        return back()->with('status', 'Pesan balasan konsultasi berhasil dikirim ke pelanggan.');
    }

    /**
     * Buka ruang chat percakapan khusus untuk pesanan ini.
     */
    public function show(Order $order, Request $request): View|JsonResponse
    {
        // Tandai pesan customer sebagai telah dibaca oleh admin
        $order->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            $order->load(['messages.user']);

            return response()->json([
                'messages' => $order->messages->map(fn ($msg) => [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'sender_name' => $msg->sender_type === 'admin' ? 'Anda (Admin)' : ($msg->user?->name ?? 'Pelanggan'),
                    'message' => $msg->message,
                    'time' => $msg->created_at->format('H:i'),
                    'read_at' => (bool) $msg->read_at,
                ]),
            ]);
        }

        $order->load(['user', 'items.product', 'messages.user']);

        return view('admin.chats.show', compact('order'));
    }

    /**
     * Admin mengirim pesan balasan ke pelanggan untuk pesanan ini.
     */
    public function store(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = $order->messages()->create([
            'user_id' => $request->user()->id,
            'sender_type' => 'admin',
            'message' => trim($validated['message']),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'sender_type' => 'admin',
                    'sender_name' => 'Anda (Admin)',
                    'message' => $message->message,
                    'time' => $message->created_at->format('H:i'),
                    'read_at' => false,
                ],
            ]);
        }

        return back()->with('status', 'Pesan balasan berhasil dikirim ke pelanggan.');
    }
}
