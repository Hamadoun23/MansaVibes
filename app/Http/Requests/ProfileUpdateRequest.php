<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:32',
                'regex:/^[0-9]+$/',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('phone');
        if (is_string($raw)) {
            $digits = preg_replace('/\D+/', '', $raw);
            if ($digits !== '') {
                $this->merge(['phone' => $digits]);
            }
        }
    }
}
