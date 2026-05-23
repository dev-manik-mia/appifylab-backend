<?php

namespace App\Data\DTOS\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    /**
     * @throws ValidationException
     */
    public static function fromRequest(array $data): self
    {
        $validator = Validator::make($data, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        return new self(
            email: $validated['email'],
            password: $validated['password'],
        );
    }
}
