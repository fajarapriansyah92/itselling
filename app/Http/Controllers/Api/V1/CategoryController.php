<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    // Ambil semua daftar kategori beserta jumlah produk aktif di masing-masing kategori
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }
}
