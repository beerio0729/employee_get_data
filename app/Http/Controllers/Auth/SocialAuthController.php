<?php

namespace App\Http\Controllers\Auth;


use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\LineSendMessageService;
use Laravel\Socialite\Facades\Socialite;


class SocialAuthController extends Controller
{
    public function redirect($provider)
    {   //dd($provider);
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $socialUser = Socialite::driver($provider)->user();

        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (!$user) {
            // สร้าง user ใหม่
            $user = User::create([
                'name'         => $socialUser->getName() ?? $socialUser->getNickname(),
                'email'        => $socialUser->getEmail(),
                'provider'     => $provider,
                'provider_id'  => $socialUser->getId(),
                'password'     => bcrypt(Str::random(16)), // หรือ set null ถ้าไม่ต้องใช้
                'role_id' => 4,
            ]);
        }

        Auth::login($user);
        LineSendMessageService::send($socialUser->getId(), ['ยินดีต้อนรับสู่เว็บอับโหลดเรซูเม่', 'คุณเข้าสู่ระบบแล้ว']);
        return redirect('/');
    }
}
