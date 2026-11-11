<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Tampilkan semua daftar ulasan produk dari pelanggan dengan filter & statistik.
     */
    public function index(Request $request): View
    {
        $query = ProductReview::query()
            ->with(['user', 'order', 'product', 'orderItem'])
            ->latest();

        if ($request->filled('rating')) {
            $query->where('rating', $request->integer('rating'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        if ($request->boolean('needs_reply')) {
            $query->whereNull('admin_reply');
        }

        if ($request->filled('search')) {
            $keyword = '%'.$request->string('search')->trim().'%';
            $query->where(function ($q) use ($keyword): void {
                $q->where('comment', 'like', $keyword)
                    ->orWhereRelation('user', 'name', 'like', $keyword)
                    ->orWhereRelation('product', 'name', 'like', $keyword)
                    ->orWhereRelation('order', 'order_number', 'like', $keyword);
            });
        }

        $reviews = $query->paginate(4)->withQueryString();

        $stats = [
            'total' => ProductReview::count(),
            'average' => round((float) (ProductReview::avg('rating') ?? 0), 1),
            'needs_reply' => ProductReview::whereNull('admin_reply')->count(),
            'five_stars' => ProductReview::where('rating', 5)->count(),
        ];

        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('admin.reviews.index', compact('reviews', 'stats', 'products'));
    }

    /**
     * Simpan atau perbarui balasan admin untuk ulasan pelanggan.
     */
    public function reply(Request $request, ProductReview $review): RedirectResponse
    {
        $validated = $request->validate([
            'admin_reply' => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        $review->update([
            'admin_reply' => $validated['admin_reply'],
            'admin_replied_at' => now(),
        ]);

        return back()->with('success', 'Balasan ulasan berhasil disimpan.');
    }

    /**
     * Hapus ulasan jika mengandung spam atau melanggar aturan.
     */
    public function destroy(ProductReview $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }
}
