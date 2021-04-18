<?php

namespace App\Http\Controllers\API;

use Exception;
use Dotenv\Validator;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
       try {
           $request->validate([
            'email' => 'email|required',
            'password' => 'password|required'
           ]);

             // Mengecek credentials (login)
             $credentials = request(['email', 'password']);
             if(!Auth::attempt([$credentials])) {
                 return ResponseFormatter::error([
                     'message' => 'Unauthorized'
                 ], 'Authentiocation Failed', 500);
             }

               // Jika hash tidak sesuai maka beri error
            $user = User::where('email', $request->email) -> firts();
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

             // Jika berhasil maka loginkan
             $tokenResult = $user->createToken('authToken')-> plainTextToken;
             return ResponseFormatter::success([
                 'access_token' => $tokenResult,
                 'token_type' => 'Bearer',
                 'user' => $user
             ], 'Authenticated');
             
       } catch (Exception $error) {
        return ResponseFormatter::error([
            'message' => 'Something went wrong',
            'error' => $error
        ], 'Authentication Failed', 500);
       }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'max:255', 'unique:users'],
                'password' => $this->passwordRules()
            ]);

                User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'github' => $request->github,
                    'phoneNumber' => $request->phoneNumber,
                    'experience' => $request->experience,
                    'occupotion' => $request->occupotion,
                    'password' => Hash::make($request->password),
                ]);

                $user = User::where('email', $request->email)->first();

                $tokenResult = $user->createToken('authToken')->plainTextToken;
                return ResponseFormatter::success([
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user
                ]);


        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }


    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');    
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(), 'Data profile user berhasil diambil');
    }


    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile Updated');
    }


    public function updatePhoto(Request $request)
    {
        $validator = Validator::make(
            $request->all(), [
                'file' => 'required|image|max:2048', 
            ]);
            if($validator->fails()) {
                return ResponseFormatter::error([
                    'error' => $validator->errors()
                ], 'Update photo failed', 401);
            }

            if ($request->file()) {
                $file = $request->file->store('assets/user', 'public');

                //simpan foto url ke database
                $user = Auth::user();
                $user->profile_photo_path = $file;
                $user->update();

                return ResponseFormatter::success([
                    $file
                ], 'File successfuly uploaded');
            }
    }
}
