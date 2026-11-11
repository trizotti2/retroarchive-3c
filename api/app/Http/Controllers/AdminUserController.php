<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminUserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'role'])
            ->latest('id')
            ->paginate(20);

        return UserResource::collection($users);
    }
}
