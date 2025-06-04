<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['only' => ['me', 'logoutApi', 'refresh']]);
    }

    // Login form untuk admin (web)
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // Proses login admin (web)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang dimasukkan tidak valid.',
        ])->withInput($request->only('email'));
    }

    // Register form admin (web)
    public function showRegisterForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    // Proses register admin (web)
    public function register(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admin'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $admin = Admin::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::guard('admin')->login($admin);

        return redirect()->route('dashboard');
    }

    // Logout admin (web)
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // Register pelanggan (API)
    public function registerApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'telepon' => ['required', 'regex:/^08[0-9]{8,11}$/', 'unique:pelanggan'],
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pelanggan = Pelanggan::create([
            'name' => $request->name,
            'telepon' => $request->telepon,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($pelanggan);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'pelanggan' => $pelanggan,
            'token' => $token
        ], 201);
    }

    // Login pelanggan (API)
    public function loginApi(Request $request)
    {
        $credentials = $request->only('telepon', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Telepon atau password salah'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token tidak dapat dibuat'], 500);
        }

        return response()->json([
            'token' => $token,
            'pelanggan' => JWTAuth::user()
        ]);
    }

    // Ambil data pelanggan yang login (API)
    public function me()
    {
        try {
            if (!$pelanggan = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Pelanggan tidak ditemukan'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token invalid'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token tidak ditemukan'], 401);
        }

        return response()->json(['pelanggan' => $pelanggan]);
    }

    // Logout pelanggan (API)
    public function logoutApi()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Logout berhasil']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Gagal logout'], 500);
        }
    }

    // Refresh token pelanggan (API)
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json(['token' => $token]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Gagal refresh token'], 500);
        }
    }
}
