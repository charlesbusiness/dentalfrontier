<?php

namespace App\Services;

use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticationService
{
    use ApiResponse;
    public function register(RegistrationRequest $request)
    {
        try {

            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return $this->successfulResponse($user, 'User registered successfully', 201);
        } catch (Exception $e) {
            $code = $e->getCode() ?? 500;
            $message  = $code === 500 ? "Registration failed" : $e->getMessage();
            return $this->failedResponse(null, $message, $code);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                $message = 'The provided credentials are incorrect.';
                return response()->json([
                    'data' => null,
                    'success' => false,
                    'message' => $message,
                    'error' => true
                ], 400);
            }

            // Revoke all existing tokens before creating a new one
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->token = $token;
            $user->token_type = 'Bearer';
            return $this->successfulResponse($user, 'Login successful');
        } catch (Exception $e) {
            $code = $e->getCode() ?? 500;
            $message  = $code === 500 ? "Registration failed" : $e->getMessage();
            return $this->failedResponse(null, $message, $code);
        }
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successfulResponse(null, 'Logged out successfully');
        } catch (Exception $e) {
            $code = $e->getCode() ?? 500;
            $message  = $code === 500 ? "Logout failed" : $e->getMessage();
            return $this->failedResponse(null, $message, $code);
        }
    }

    /**
     * Get authenticated user details.
     */
    public function user(Request $request)
    {
        return $this->successfulResponse([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }
}
