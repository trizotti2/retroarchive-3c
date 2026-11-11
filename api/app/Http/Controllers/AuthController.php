<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->auth->register($request, $request->validated());

        return $this->accountResponse($result)->response()->setStatusCode(201);
    }

    public function login(LoginRequest $request): UserResource
    {
        return $this->accountResponse($this->auth->login($request, $request->validated()));
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout($request);

        return response()->noContent();
    }

    private function accountResponse(array $result): UserResource
    {
        return (new UserResource($result['user']))->additional(['cart_adjustments' => $result['adjustments']]);
    }
}
