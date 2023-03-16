<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="LiberflyAPI",
 *     version="1.0.0",
 *     description="API para confirmação de habilidades."
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Realiza login de usuário",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOi..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_at", type="string", format="date-time", example="2023-03-17 08:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Credenciais inválidas"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Dados de entrada inválidos"
     *     )
     * )
     */
    public function login(Request $request)
    {
        // Validação de login
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Caso ocorra erro nas validações retorna o erro aqui
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Pegando o que veio do request
        $credentials = request(['email', 'password']);

        // Verificando o que veio do request
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = $request->user();
        // Criando TokenAcess
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        // Expirando token 1 semana apos o cadastro
        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }
        // Salvando token
        $token->save();
        // Resposta de suesso
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Cria um novo usuário",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Usuário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOi..."),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Dados de entrada inválidos"
     *     )
     * )
     */
    public function register(Request $request)
    {
        // Validações para cadastro de usuarios
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);
        // Caso ocorra erro nas validações retorna o erro aqui
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // Caso dê tudo certo o usuario é cadastrado
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        // Criando token de acesso
        $tokenResult = $user->createToken('Personal Access Token');
        // Return de sucesso
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }
}
