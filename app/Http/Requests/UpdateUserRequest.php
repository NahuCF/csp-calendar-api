<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        // Handle avatar and waiver updates
        if ($this->has('signed_waiver') || $this->has('avatar')) {
            return [
                'signed_waiver' => ['boolean'],
                'signature' => [
                    'required_with:signed_waiver',
                    'string',
                    'min:3',
                    'max:40',
                    'regex:/^[\p{L}\s\-\'\.]+$/u', // Allows letters, spaces, hyphens, apostrophes, dots
                ],
                'avatar' => [
                    'nullable',
                    File::types(['jpeg', 'jpg', 'png', 'webp', 'gif', 'avif'])
                        ->min(128)
                        ->max(20 * 1024 * 1024),
                    'dimensions:min_width=100,min_height=100,max_width=10000,max_height:10000',
                    'image',
                    'mimes:jpeg,jpg,png,webp,gif,avif',
                    'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/avif',
                ],
            ];
        }

        // Base rules for user details update
        $rules = [
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{L}\s\-\'\.]+$/u', // Allows letters, spaces, hyphens, apostrophes, dots
                'not_regex:/^\s*$/', // Prevents whitespace-only values
            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[\p{L}\s\-\'\.]+$/u',
                'not_regex:/^\s*$/',
            ],
            'country' => [
                'required',
                'string',
                'min:2',
                'max:40',
            ],
        ];

        // Get the current user's email and phone
        $user = $this->route('user');
        $hasEmail = ! empty($user->email);
        $hasPhone = ! empty($user->phone);

        // Handle email/phone validation based on current user state
        if (! $hasEmail && $this->has('email')) {
            // User registered with phone, adding email
            $rules['email'] = [
                'required',
                'string',
                'email:rfc,dns,spoof,filter',
                'max:255',
                Rule::unique('users')->ignore($userId),
                'not_regex:/\.(ru|cn)$/i', // Optional: Block specific TLDs if needed
                function ($attribute, $value, $fail) {
                    // Prevent disposable email providers
                    if (str_contains($value, 'tempmail') || str_contains($value, 'throwaway')) {
                        $fail('Disposable email addresses are not allowed.');
                    }
                },
            ];
            $rules['phone'] = [
                'sometimes',
                'string',
                'min:6',
                'max:20',
                'regex:/^\+?[1-9][0-9\-\(\)\s\.]{4,18}[0-9]$/', // Flexible international format
                Rule::unique('users')->ignore($userId),
            ];
        } elseif (! $hasPhone && $this->has('phone')) {
            // User registered with email, adding phone
            $rules['phone'] = [
                'required',
                'string',
                'min:6',
                'max:20',
                'regex:/^\+?[1-9][0-9\-\(\)\s\.]{4,18}[0-9]$/', // Flexible international format
                Rule::unique('users')->ignore($userId),
            ];
            $rules['email'] = [
                'sometimes',
                'string',
                'email:rfc,dns,spoof,filter',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ];
        } else {
            // Regular update - make both fields optional but validated if present
            $rules['email'] = [
                'sometimes',
                'string',
                'email:rfc,dns,spoof,filter',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ];
            $rules['phone'] = [
                'sometimes',
                'string',
                'min:6',
                'max:20',
                'regex:/^\+?[1-9][0-9\-\(\)\s\.]{4,18}[0-9]$/', // Flexible international format
                Rule::unique('users')->ignore($userId),
            ];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Ensure at least one contact method is present
            if (! $this->has('avatar')) {
                if (empty($this->input('email')) && empty($this->input('phone'))) {
                    $validator->errors()->add(
                        'contact',
                        'At least one contact method (email or phone) is required.'
                    );
                }
            }

            // Check for valid phone number format if present
            if ($phone = $this->input('phone')) {
                // Remove all formatting characters
                $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);

                // Ensure it starts with + or a number
                if (! preg_match('/^\+?\d+$/', $cleanPhone)) {
                    $validator->errors()->add(
                        'phone',
                        'Phone number must contain only numbers, optionally starting with +'
                    );
                }

                // Ensure reasonable length after cleaning
                if (strlen($cleanPhone) < 6 || strlen($cleanPhone) > 15) {
                    $validator->errors()->add(
                        'phone',
                        'Phone number must be between 6 and 15 digits'
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.regex' => 'First name may only contain letters, spaces, hyphens, apostrophes, and dots.',
            'last_name.regex' => 'Last name may only contain letters, spaces, hyphens, apostrophes, and dots.',
            'country.regex' => 'Country must be a valid ISO 3166-1 alpha-2 country code.',
            'phone.regex' => 'Please enter a valid phone number.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'phone.unique' => 'This phone number is already in use.',
            'signature.regex' => 'Signature may only contain letters, spaces, hyphens, apostrophes, and dots.',
        ];
    }
}
