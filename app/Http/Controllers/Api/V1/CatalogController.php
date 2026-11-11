<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($request->string('category')->isNotEmpty(), function ($query) use ($request): void {
                $query->whereHas('category', function ($categoryQuery) use ($request): void {
                    $categoryQuery->where('slug', $request->string('category')->toString());
                });
            })
            ->orderBy('name')
            ->get();

        return ProductResource::collection($products);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug): ProductResource
    {
        $product = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProductResource($product);
    }
}
