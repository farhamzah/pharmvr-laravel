<?php

namespace App\Http\Requests\Api\V1\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'full_name'   => 'sometimes|required|string|max:255',
            'email'       => 'sometimes|required|string|email|max:255|unique:users,email,' . $userId,
            'phone_number' => 'nullable|string|max:30',
            'university'  => 'nullable|string|max:255',
            'semester'    => 'nullable|integer|min:1|max:20',
            'nim'         => 'nullable|string|max:100',
            'avatar'      => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'avatar.max' => 'Ukuran foto profil terlalu besar. Maksimal adalah 1MB (1024KB) untuk menjaga stabilitas aplikasi.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format gambar yang didukung hanya: jpeg, png, jpg.',
        ];
    }
}
