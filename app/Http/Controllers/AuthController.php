<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="expires_at", type="string", format="date-time")
     *         )
     *     )
     * )
     */
    public function register(Request $request){
        $data = $request->validate([
            'name'=> 'required|max:255',
            'email'=> 'required|email|unique:users',
            'password'=> 'required|confirmed'
        ]);
        $user = User::create($data);

        $tokenResult = $user->createToken($user->name);
        $plainTextToken = $tokenResult->plainTextToken;
        $tokenId = explode('|', $plainTextToken)[0]; // obtener el ID del token

        // tiempo de expiracion
        $expiresAt = now()->addHours(2);

        // actualizar fecha expiración
        DB::table('personal_access_tokens')
        ->where('id', $tokenId)
        ->update(['expires_at' => $expiresAt]);


        return [
            'user'=> $user,
            'token'=> $plainTextToken,
            'expires_at' => $expiresAt
        ];
    }
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="expires_at", type="string", format="date-time")
     *         )
     *     )
     * )
     */

    public function login(Request $request){
         $request->validate([
            'email'=> 'required|email|exists:users',
            'password'=> 'required'
        ]);
 
        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return [
                'message' => 'The provided credentials are incorrect.'
            ];
        }

        $tokenResult = $user->createToken($user->name);
        $plainTextToken = $tokenResult->plainTextToken;
        $tokenId = explode('|', $plainTextToken)[0]; // obtener el ID del token

        // tiempo de expiracion
        $expiresAt = now()->addHours(2);

        // actualizar fecha expiración
        DB::table('personal_access_tokens')
        ->where('id', $tokenId)
        ->update(['expires_at' => $expiresAt]);


        return [
            'user'=> $user,
            'token'=> $plainTextToken,
            'expires_at' => $expiresAt
        ];
    }
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You are logged out")
     *         )
     *     )
     * )
     */
    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return[
            'message'=> 'You are logged out'
        ];
    }
    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Enviar enlace de restablecimiento de contraseña",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Correo enviado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Correo enviado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error enviando correo",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error enviando correo")
     *         )
     *     )
     * )
    */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Correo enviado'])
            : response()->json(['message' => 'Error enviando correo'], 500);
    }


    
    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Enviar enlace de restablecimiento de contraseña",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Correo enviado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Correo enviado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error enviando correo",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error enviando correo")
     *         )
     *     )
     * )
    */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Contraseña actualizada'])
            : throw ValidationException::withMessages(['email' => [__($status)]]);
    }
}
