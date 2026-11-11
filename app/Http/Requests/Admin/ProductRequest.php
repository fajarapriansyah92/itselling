<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $type = $this->input('type');
        if (blank($type)) {
            $type = Product::inferTypeFromCategory(
                $this->filled('category_id') ? (int) $this->input('category_id') : null,
                (string) $this->input('unit')
            )->value;
        }

        $sku = $this->input('sku');
        if (blank($sku)) {
            $sku = Product::generateSkuForType($type);
        }

        $shortDesc = $this->input('short_description');
        if (blank($shortDesc) && $this->filled('description')) {
            $shortDesc = Str::limit(trim(strip_tags((string) $this->input('description'))), 200, '...');
        }

        $brand = $this->filled('brand') ? trim((string) $this->input('brand')) : null;
        $stockInput = $this->filled('stock') ? (int) $this->input('stock') : null;
        $unitInput = $this->filled('unit') ? trim((string) $this->input('unit')) : '';
        if ($unitInput === '') {
            $unitInput = $type === ProductType::Service->value ? 'jasa' : 'unit';
        }

        $this->merge([
            'type' => $type,
            'brand' => $brand !== '' ? $brand : null,
            'unit' => $unitInput,
            'slug' => Str::slug((string) $this->input('name')),
            'sku' => $sku,
            'short_description' => $shortDesc,
            'is_active' => $this->boolean('is_active'),
            'stock' => $type === ProductType::Service->value ? null : $stockInput,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', Rule::unique('products', 'slug')->ignore($this->route('product'))],
            'type' => ['required', Rule::enum(ProductType::class)],
            'brand' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:30'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,bmp,svg', 'max:10240'],
            'remove_image' => ['nullable', 'boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.unique' => 'Nama produk sudah digunakan.',
            'stock.required' => 'Stok wajib diisi untuk produk hardware dan software.',
        ];
    }
}
