<?php

namespace App\Http\Requests\Draft;

use Illuminate\Foundation\Http\FormRequest;

class StoreDraftRequest extends FormRequest
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
        return [
            'title' => 'required',
            'body' => 'nullable',
            'privacy' => 'nullable',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,mp4|max:30720'
        ];
    }
}
