<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        $category = $this->route('category');
        return $this->user()?->can('update', $category) ?? true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array{
        $category = $this->route('category');

        return [
            'name'        => ['required', 'string', 'max:150'],
            'slug'        => ['nullable', 'string', 'max:180', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'color'       => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ];
    }
}
