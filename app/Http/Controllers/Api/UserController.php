<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;

class UserController extends Controller
{
     /**
     * GetProfile User
     * @param Request $request
     * @return User
     */
    public function getProfile(Request $request)
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
            return response()->json([
                'status' => true,
                'message' => 'User Data Successfully',
                'data' => $user,
                // 'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        }

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

     /**
     * Update Profile
     * @param Request $request
     * @return User
     */
    public function updateProfile(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(),
            [
                'phone' => ['required' , 'numeric' , 'min:11'],
                'pictur' => ['sometimes'],
            ]);
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            if(Auth::user()){
            $user = Auth::user();
            $user->update([
                'phone' => $request->phone,
            ]);

            if ($request->hasFile('pictur')) {
                $image = $request->file('pictur');
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $request->pictur->move(public_path('uploads/users/' . $user->id), $filename);
                $user->pictur = '/uploads/users/' . $user->id . '/' . $filename;
                $user->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'User Update Profile  Successfully',
                'user' => $user,
            ], 200);
        }

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
     /**
     * Lgout
     * @param Request $request
     * @return User
     */
    public function logout(Request $request)
    {
        try {

            if(Auth::user()){
            $user = Auth::user();
            Auth::user()->currentAccessToken()->delete();;
            return response()->json([
                'status' => true,
                'message' => 'User Logout Successfully',
            ], 200);
        }

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}


