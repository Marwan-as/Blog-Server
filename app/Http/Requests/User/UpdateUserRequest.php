<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $user_id = $this->route('user') ? $this->route('user')->id : null;
        return [
            'name' => 'sometimes|required|string|max:255|unique:users,name,' . $user_id,
            'email' => 'sometimes|required|email|unique:users,email,' . $user_id,
            'showEmail' => 'nullable',
            'biography' => 'nullable',
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:30720',
            'coverImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:30720'
        ];
    }
}
