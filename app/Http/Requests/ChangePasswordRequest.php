<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Ensure the user is authenticated
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'confirmed', // Ensures there is a 'new_password_confirmation' field
                Password::min(8) // Minimum 8 characters
                ->mixedCase() // Contains both uppercase and lowercase letters
                ->letters() // Contains letters
                ->numbers() // Contains numbers
                ->symbols() // Contains symbols
                ->uncompromised(), // Not found in data leaks
            ],
        ];
    }

    /**
     * Customize the error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'current_password.required' => 'Please enter your current password.',
            'new_password.required' => 'Please enter a new password.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
        ];
    }
}
