<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateInstructorRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display all instructors
     */
    public function index()
    {
        $users = User::where('role', '=', 'instructor', false)
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }

    /**
     * Create new instructor
     */
    public function createInstructor(CreateInstructorRequest $request)
    {
        $user = User::create([
            'name' => $request->name,

            'email' => strtolower($request->email),

            'phone' => $request->phone,

            'gender' => $request->gender,

            'role' => 'instructor',

            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Instructor created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Show single instructor
     */
    public function show(string $id)
    {
        $user = User::where('role', '=', 'instructor', false)
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    /**
     * Delete instructor
     */
    public function destroy(string $id)
    {
        $user = User::where('role', '=', 'instructor', false)
            ->findOrFail($id);

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Instructor deleted successfully'
        ]);
    }
}
