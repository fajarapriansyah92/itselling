<?php

namespace App\Http\Requests\Api;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productIds = collect($this->input('items', []))->pluck('product_id')->filter()->all();
        $isAllDigital = false;

        if (! empty($productIds)) {
            $isAllDigital = Product::query()
                ->whereIn('id', $productIds)
                ->get()
                ->every(fn (Product $p) => $p->type === ProductType::Software);
        }

        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:20'],
            // Jika semua item adalah software digital, alamat fisik tidak wajib (opsional)
            'shipping_address' => [$isAllDigital ? 'nullable' : 'required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['nullable', 'string', 'in:credit_card,bank_transfer,ewallet'],
            'payment_channel' => ['nullable', 'string', 'max:50'],
        ];
    }
}
