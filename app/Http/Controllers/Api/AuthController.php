<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required',
                    'phone' => 'required',
                    'pictur' => 'required',
                ]
            );


            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'guard' => 'user',
                'password' => Hash::make($request->password)
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
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function checkEmail(Request $request)
    {

        try {
            if (Auth::guest()) {
                $validator = validator()->make($request->all(), [
                    'email'  => 'required|string|email|max:191|exists:users'
                ]);
                if ($validator->fails()) {
                    $errorData = $validator->errors();
                    return response()->json(['status' => false, 'message' => $errorData]);
                }
                $user = User::where('email', $request->email)->first();
                if ($user != null) {
                    $code = rand(111111, 999999);
                    $updateUser = $user->update(['pin_code' => $code]);
                    if ($updateUser) {
                        //send email
                        // Mail::to($user->email)
                        // 	//->bcc("mahdyadel00@gmail.com")
                        // 	->send(new ResetPassword($code));
                        return response()->json([
                            'status' => true,
                            'message' => 'Your code has been sent successfully, please check your email now!',
                            'data' => $user,
                            'pin_code' => $code,
                        ], 200);
                    } else {
                        return response()->json(['status' => false, 'message' => 'orry, an error has occurred, please try again!']);
                    }
                } else {
                    response()->json(['status' => false, 'message' => 'Sorry, there is no account associated with this email!']);
                }
            } else {
                response()->json(['status' => false, 'message' => 'User is logged in']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function checkCode(Request $request)
    {
        try {
            $validator = validator()->make($request->all(), [
                'pin_code' => 'required|exists:users',
            ]);

            if ($validator->fails()) {
                $errorData = $validator->errors();
                return $this->sendError($errorData->first(), $errorData);
            } else {
                $user = User::where('pin_code', $request->pin_code)->where('pin_code', '!=', 0)->first();
                if ($user) {
                    return response()->json([
                        'status' => true,
                        'message' => 'The code is correct',
                        'data' => $user,
                        'pin_code' => $user->pin_code,
                    ], 200);
                } else {
                    return $this->sendError('User is logged in');
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validator = validator()->make($request->all(), [
                'pin_code' => 'required|exists:users',
                'password' => 'required|confirmed|min:6'
            ]);
            if ($validator->fails()) {
                $errorData = $validator->errors();
                return response()->json(['status' => false, 'message' => $errorData]);
            } else {
                $user = User::where('pin_code', $request->pin_code)->where('pin_code', '!=', 0)->first();
                if ($user) {
                    $user->update([
                        "password" => bcrypt($request->password),
                        "pin_code" => null
                    ]);
                    if ($user->save()) {
                        return response()->json([
                            'status' => true, 'message' => 'The password has been reset successfully', 'data' => $user,
                        ], 200);
                    } else {
                        return response()->json(['status' => false, 'message' => 'Sorry, an error has occurred, please try again!']);
                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'Sorry, this code is invalid!']);
                }
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }
}
