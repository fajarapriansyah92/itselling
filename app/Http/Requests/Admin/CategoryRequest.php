<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
        if (is_array($this->input('categories'))) {
            $items = collect($this->input('categories'))
                ->map(function ($item): array {
                    $name = trim((string) ($item['name'] ?? ''));
                    $description = isset($item['description']) ? trim((string) $item['description']) : null;

                    return [
                        'name' => $name,
                        'slug' => $name !== '' ? Str::slug($name) : '',
                        'description' => $description !== '' ? $description : null,
                    ];
                })
                ->filter(fn (array $item): bool => $item['name'] !== '')
                ->values()
                ->all();

            $this->merge(['categories' => $items]);

            return;
        }

        $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->has('categories')) {
            return [
                'categories' => ['required', 'array', 'min:1', 'max:20'],
                'categories.*.name' => ['required', 'string', 'max:100', 'distinct:ignore_case'],
                'categories.*.slug' => ['required', 'string', 'distinct', Rule::unique('categories', 'slug')],
                'categories.*.description' => ['nullable', 'string', 'max:1000'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', Rule::unique('categories', 'slug')->ignore($this->route('category'))],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.unique' => 'Nama kategori sudah digunakan.',
            'categories.required' => 'Minimal 1 nama kategori wajib diisi.',
            'categories.min' => 'Minimal 1 nama kategori wajib diisi.',
            'categories.*.name.distinct' => 'Terdapat nama kategori yang sama pada form input.',
            'categories.*.slug.distinct' => 'Terdapat nama kategori yang sama pada form input.',
            'categories.*.slug.unique' => 'Salah satu nama kategori sudah terdaftar di database.',
        ];
    }
}
