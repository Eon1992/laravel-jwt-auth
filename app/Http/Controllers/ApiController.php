<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    protected $response = [];
    protected $error_msg = null;

    public function register(Request $request)
    {
    	//Validate data
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {

            $this->error_msg = $validator->errors()->toArray();
            $this->error_msg = array_values($this->error_msg)[0][0];

            $this->response = [
                "status" => "error",
                "error" => true,
                "response_code" => 400,
                "message" => $this->error_msg,
            ];

        } else {

            //Request is valid, create new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            //User created, return success response

            $this->response = [
                "status" => "success",
                "error" => false,
                "response_code" => 200,
                "message" => "User created successfully",
                "data" => $user
            ];
        }

        return response($this->response, $this->response['response_code']);

    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {

            $this->error_msg = $validator->errors()->toArray();
            $this->error_msg = array_values($this->error_msg)[0][0];

            $this->response = [
                "status" => "error",
                "error" => true,
                "response_code" => 400,
                "message" => $this->error_msg,
            ];

            return response($this->response, $this->response['response_code']);
        }

        //Request is validated
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {

                $this->response = [
                    "status" => "error",
                    "error" => true,
                    "response_code" => 400,
                    "message" => 'Login credentials are invalid.',
                ];

                return response($this->response, $this->response['response_code']);

            }
        } catch (JWTException $e) {
    	return $credentials;
            $this->response = [
                "status" => "error",
                "error" => true,
                "response_code" => 500,
                "message" => 'Could not create token.',
            ];

            return response($this->response, $this->response['response_code']);
        }

 		//Token created, return with success response and jwt token

         $this->response = [
            "status" => "success",
            "error" => false,
            "response_code" => 200,
            "message" => 'Success.',
            "token" => $token,
        ];

        return response($this->response, $this->response['response_code']);

    }

    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

		//Request is validated, do logout
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['user' => $user]);
    }
}
