<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // brute force protection — هم IP هم email
        $ipKey    = 'login_attempts:ip:' . $request->ip();
        $emailKey = 'login_attempts:email:' . $data['email'];

        $ipAttempts    = Cache::get($ipKey, 0);
        $emailAttempts = Cache::get($emailKey, 0);

        if ($ipAttempts >= 5 || $emailAttempts >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'تعداد تلاش‌های ناموفق زیاد است. لطفاً ۱۵ دقیقه دیگر امتحان کنید.',
            ], 429);
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            Cache::put($ipKey,    $ipAttempts + 1,    now()->addMinutes(15));
            Cache::put($emailKey, $emailAttempts + 1, now()->addMinutes(15));

            return response()->json([
                'success' => false,
                'message' => 'ایمیل یا رمز عبور اشتباه است.',
                'errors'  => ['email' => ['ایمیل یا رمز عبور اشتباه است.']],
            ], 401);
        }

        // login موفق — attempts رو پاک کن
        Cache::forget($ipKey);
        Cache::forget($emailKey);

        $user->tokens()->where('name', 'api')->delete();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success'    => true,
            'data'       => $this->userArray($user),
            'token'      => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function register(Request $request)
    {
        // rate limit ثبت‌نام — هر IP حداکثر ۳ اکانت در ساعت
        $registerKey = 'register_attempts:' . $request->ip();
        $attempts    = Cache::get($registerKey, 0);

        if ($attempts >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'تعداد ثبت‌نام از این IP زیاد است. لطفاً بعداً امتحان کنید.',
            ], 429);
        }

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
            'company'  => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'phone'    => $data['phone'] ?? null,
            'company'  => $data['company'] ?? null,
            'role'     => 'customer',
        ]);

        Cache::put($registerKey, $attempts + 1, now()->addHour());

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success'    => true,
            'data'       => $this->userArray($user),
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'با موفقیت خارج شدید.',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->userArray($request->user()),
        ]);
    }

    private function userArray(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'company'    => $user->company,
            'role'       => $user->role ?? 'customer',
            'avatar'     => $user->avatar,
            'created_at' => $user->created_at->toIso8601String(),
        ];
    }
}
