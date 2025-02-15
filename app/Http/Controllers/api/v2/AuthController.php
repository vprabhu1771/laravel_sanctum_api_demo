<?php

namespace App\Http\Controllers\api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user->createToken($request->device_name)->plainTextToken;
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        return 'Tokens are deleted';
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'device_name' => 'required|string', // Add device_name validation
            ]);

            // $request->validate([
            //     'name' => 'required|string|max:255',
            //     'email' => 'required|email|unique:users,email',
            //     'password' => 'required|string|min:6',
            //     'device_name' => 'required|string', // Add device_name validation
            // ]);

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);

            // Issue a token with Sanctum and attach the device_name
            // $token = $user->createToken($request->input('device_name'))->plainTextToken;

            // return response()->json(['message' => 'User registered successfully', 'user' => $user, 'token' => $token], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        }

    }

    /**
     * Get the current authenticated user.
     */
    public function getUser(Request $request)
    {
        $user = $request->user();
        $user->image_path = url('/storage/' . $user->image_path);
        return response()->json($user);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update_profile(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6',
                'device_name' => 'required|string',
            ]);

            $data = [];
            if ($request->filled('name')) {
                $data['name'] = $request->input('name');
            }
            if ($request->filled('email')) {
                $data['email'] = $request->input('email');
            }
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->input('password'));
            }
            $user->update($data);

            return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function upload_profile_pic(Request $request)
    {
        try {
            $request->validate([
                // 'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'file' => 'required|image|mimes:jpeg,png,jpg,gif',
            ]);

            $user = auth()->user();

            // Delete old image if exists
            if ($user->image_path) {
                Storage::delete($user->image_path);
            }

            // Store new image
            $path = $request->file('file')->store('profile_pics', 'public');

            // Update user's image path
            $user->update(['image_path' => $path ]);

            return response()->json(['message' => 'Profile picture uploaded successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        }
    }
}
