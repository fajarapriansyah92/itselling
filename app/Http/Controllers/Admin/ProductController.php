<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function generateSku(Request $request): JsonResponse
    {
        $type = $request->query('type');
        if (blank($type) && $request->filled('category_id')) {
            $type = Product::inferTypeFromCategory((int) $request->query('category_id'));
        }

        $sku = Product::generateSkuForType($type);

        return response()->json(['sku' => $sku]);
    }

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('search')->trim()->toString().'%';

                $query->where(fn ($query) => $query->where('name', 'like', $keyword)->orWhere('sku', 'like', $keyword)->orWhere('brand', 'like', $keyword));
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Product(['is_active' => true, 'unit' => 'unit', 'type' => ProductType::Hardware]));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::create([
            ...$request->safe()->except('image'),
            'image_path' => $request->file('image')?->store('products', 'public'),
        ]);

        return redirect()->route('admin.products.index')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return $this->form($product);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->boolean('remove_image')) {
            if ($product->image_path !== null) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = null;
        } elseif ($request->hasFile('image')) {
            if ($product->image_path !== null) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        if ($request->boolean('stay')) {
            return redirect()->route('admin.products.edit', $product)->with('status', 'Produk berhasil diperbarui.');
        }

        return redirect()->route('admin.products.index')->with('status', 'Produk berhasil diperbarui.');
    }

    public function uploadImage(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif,bmp,svg,avif', 'max:10240'],
        ], [
            'image.required' => 'Pilih file gambar terlebih dahulu.',
            'image.image' => 'File yang dipilih harus berupa file gambar valid.',
            'image.max' => 'Ukuran gambar maksimal 10 MB.',
        ]);

        if ($product->image_path !== null) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->update([
            'image_path' => $request->file('image')->store('products', 'public'),
        ]);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Gambar produk berhasil ditambahkan!');
    }

    public function destroyImage(Product $product): RedirectResponse
    {
        if ($product->image_path !== null) {
            Storage::disk('public')->delete($product->image_path);
            $product->update(['image_path' => null]);
        }

        return redirect()->route('admin.products.edit', $product)->with('status', 'Gambar produk berhasil dihapus!');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            $product->update(['is_active' => false]);

            return back()->with('status', 'Produk sudah pernah dipesan, sehingga dinonaktifkan (bukan dihapus).');
        }

        if ($product->image_path !== null) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Produk berhasil dihapus.');
    }

    private function form(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'types' => ProductType::cases(),
        ]);
    }
}
