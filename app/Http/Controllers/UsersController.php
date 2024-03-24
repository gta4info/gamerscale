<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function list(string $query = null): JsonResponse
    {
        if($query) {
            $list = User::where('name', 'ilike', "%$query%")
                ->orWhere('epic_name', 'ilike', "%$query%")
                ->get();
        } else {
            $list = User::all();
        }

        return response()->json(['users' => $list]);
    }
}
