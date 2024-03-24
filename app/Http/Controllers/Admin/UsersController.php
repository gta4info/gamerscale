<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends BaseController
{
    public function list(string $query = null): Response
    {
        if($query) {
            $users = DB::select('SELECT epic_name, name, created_at, id FROM users WHERE name ILIKE :q OR epic_name ILIKE :q ORDER BY created_at DESC', ['q' => "%$query%"]);
        } else {
            $users = DB::select('SELECT epic_name, name, created_at, id FROM users ORDER BY created_at DESC');
        }

        return Inertia::render('Admin/Users/List', ['users' => $users]);
    }

    public function searchByName(Request $request, string $query = ''): JsonResponse
    {
        $users = DB::table('users')
            ->select(['name', 'id'])
            ->where('name', 'ilike', "%$query%");

        if($request->get('existedIds')) {
            $users->whereNotIn('id', explode(',', $request->get('existedIds', 0)));
        }

        return response()->json([
            'users' => $users->get()
        ]);
    }
}
