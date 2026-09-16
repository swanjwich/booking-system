<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480', 'multiple_of:5'],
            'buffer_minutes' => ['required', 'integer', 'min:0', 'max:120', 'multiple_of:5'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'is_active' => ['boolean'],
        ];
    }

    public function serviceData(): array
    {
        return [
            'name' => $this->string('name')->trim()->toString(),
            'description' => $this->input('description'),
            'duration_minutes' => $this->integer('duration_minutes'),
            'buffer_minutes' => $this->integer('buffer_minutes'),
            'price_amount' => (int) round($this->float('price') * 100),
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
