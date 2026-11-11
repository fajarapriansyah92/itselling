<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductReviewResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /**
     * Mengambil daftar ulasan dan statistik rating untuk sebuah produk.
     */
    public function index(Product $product): JsonResponse
    {
        $reviews = $product->reviews()
            ->with(['user', 'orderItem'])
            ->latest()
            ->paginate(15);

        $totalReviews = $product->reviews()->count();
        $avgRating = round((float) ($product->reviews()->avg('rating') ?? 0), 1);

        $breakdown = [
            5 => $product->reviews()->where('rating', 5)->count(),
            4 => $product->reviews()->where('rating', 4)->count(),
            3 => $product->reviews()->where('rating', 3)->count(),
            2 => $product->reviews()->where('rating', 2)->count(),
            1 => $product->reviews()->where('rating', 1)->count(),
        ];

        return response()->json([
            'summary' => [
                'rating_average' => $avgRating,
                'reviews_count' => $totalReviews,
                'breakdown' => $breakdown,
            ],
            'data' => ProductReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Membuat atau memperbarui ulasan dari pesanan yang sudah selesai.
     */
    public function store(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        if ($order->status !== OrderStatus::Completed && $order->status !== OrderStatus::Delivered) {
            return response()->json([
                'message' => 'Pesanan belum selesai. Anda hanya dapat memberi ulasan setelah pesanan berstatus Selesai.',
            ], 422);
        }

        $validated = $request->validate([
            'order_item_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $item = $order->items()->findOrFail($validated['order_item_id']);

        $review = ProductReview::updateOrCreate(
            [
                'order_item_id' => $item->id,
            ],
            [
                'user_id' => $request->user()->id,
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]
        );

        return response()->json([
            'message' => 'Ulasan berhasil disimpan.',
            'data' => new ProductReviewResource($review->load(['user', 'orderItem', 'product'])),
        ], 201);
    }
}
