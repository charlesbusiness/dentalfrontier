<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Services\AuthenticationService;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    protected $authenticationService;
    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }


    /**
     * Register a new user.
     */
    public function register(RegistrationRequest $request)
    {
        return $this->authenticationService->register($request);
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request)
    {
        return $this->authenticationService->login($request);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request)
    {
        return $this->authenticationService->logout($request);
    }

    /**
     * Get authenticated user details.
     */
    public function user(Request $request)
    {
        return $this->authenticationService->user($request);
    }
}
