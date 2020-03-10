<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     tags={"Auth"},
     *
     *     @OA\Parameter(
     *        name="name",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *
     *     @OA\Parameter(
     *        name="email",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *
     *     @OA\Parameter(
     *        name="password",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *
     *      @OA\Parameter(
     *        name="c_password",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *
     *      @OA\Parameter(
     *        name="client_name",
     *        in="query",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *     @OA\Response(
     *        response="200",
     *        description="Inscris un nouvel utilisateur et retourne un token en cas de succès.",
     *        @OA\MediaType(
     *            mediaType="application/json",
     *        )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'c_password' => 'required|same:password',
                'client_name' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            return response()->json(['error' => ['email' => ['Email already in use.']]], 401);
        }

        $input = $request->all();

        // Ici on sécure juste pour ctr
        if ($input['client_name'] != "ctr-api") {
            return response()->json(['error' => ['client_name' => ['Client name is incorrect.']]], 401);
        }

        $input['password'] = Hash::make($input['password'], [
            'rounds' => 12
        ]);
        $user = User::create($input);
        $success['token'] =  $user->createToken($request->client_name)->plainTextToken;

        return response()->json(['success' => $success], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     tags={"Auth"},
     *
     *     @OA\Parameter(
     *        name="email",
     *        in="query",
     *        description="Email de test : test@test.fr",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *
     *     @OA\Parameter(
     *        name="password",
     *        in="query",
     *        description="Mot de passe de test : test",
     *        required=true,
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *
     *      @OA\Parameter(
     *        name="client_name",
     *        in="query",
     *        required=true,
     *        description="Client valide : ctr-api",
     *        @OA\Schema(
     *           type="string"
     *        )
     *    ),
     *     @OA\Response(
     *        response="200",
     *        description="Inscris un nouvel utilisateur et retourne un token en cas de succès.",
     *        @OA\MediaType(
     *            mediaType="application/json",
     *        )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // Ici on sécure juste pour ctr
        if ($request->client_name != "ctr-api") {
            return response()->json(['error' => ['client_name' => ['Client name is incorrect.']]], 401);
        }

        if(!Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            return response()->json(['error' => ['login' => ['Invalid credentials.']]], 401);
        }

        $user = Auth::user();
        $success['token'] =  $user->createToken($request->client_name)->plainTextToken;

        return response()->json(['success' => $success], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user",
     *     tags={"User"},
     *
     *     @OA\Response(
     *        response="200",
     *        description="Récupère les informations de l'utilisateur connecté.",
     *        @OA\MediaType(
     *            mediaType="application/json",
     *        )
     *     )
     * )
     */
    public function user(Request $request)
    {
        $user = Auth::user();
        return response()->json($request->user(), 200);
    }

}
