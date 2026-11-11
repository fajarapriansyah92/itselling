<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * Daftar produk aktif, dapat difilter berdasarkan kategori, tipe, dan kata kunci.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Ambil produk yang aktif beserta relasi kategori dan rata-rata rating ulasan
        $products = Product::query()
            ->active()
            ->with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            // Filter berdasarkan slug kategori jika dipilih
            ->when($request->filled('category'), fn ($query) => $query->whereRelation('category', 'slug', $request->string('category')->toString()))
            // Filter berdasarkan tipe (hardware, software, atau service)
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            // Filter pencarian berdasarkan nama produk atau merek
            ->when($request->filled('search'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('search')->trim()->toString().'%';

                $query->where(fn ($query) => $query->where('name', 'like', $keyword)->orWhere('brand', 'like', $keyword));
            })
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 20), 50))
            ->withQueryString();

        return ProductResource::collection($products);
    }

    // Tampilkan detail satu produk berdasarkan slug-nya
    public function show(Product $product): ProductResource
    {
        // Kalau produk sedang dinonaktifkan admin, kembalikan 404
        abort_unless($product->is_active, 404);

        $product->load('category')->loadAvg('reviews', 'rating')->loadCount('reviews');

        return new ProductResource($product);
    }
}
