<?php

namespace App\Http\Controllers\Auth;


use App\Models\User;
use Illuminate\Support\Str;
use App\Events\ProcessEmpDocEvent;
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
        $email = $socialUser->getEmail();
        
        if (!$user) 
        {
            // สร้าง user ใหม่
            if (blank($email)) {
                // ถ้าไม่มี email → สร้าง user ใหม่ *เสมอ*
                $user = User::create([
                    'name'        => $socialUser->getName() ?? $socialUser->getNickname(),
                    'email'       => null,
                    'provider'    => $provider,
                    'provider_id' => $socialUser->getId(),
                    'password'    => bcrypt(Str::random(16)),
                    'role_id'     => 4,
                ]);
            } else {
                // ถ้า provider ส่ง email → ใช้อัปเดต/สร้างแบบยึด email
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name'        => $socialUser->getName() ?? $socialUser->getNickname(),
                        'provider'    => $provider,
                        'provider_id' => $socialUser->getId(),
                        'password'    => bcrypt(Str::random(16)),
                        'role_id'     => 4,
                    ]
                );
            }

            Auth::login($user);
            LineSendMessageService::send($socialUser->getId(), ['ยินดีต้อนรับสู่เว็บอับโหลดเรซูเม่', 'กรุณาอับเดตข้อมูลโปรไฟล์ให้ครบถ้วน']);
            return redirect('/profile');
        } else {
            Auth::login($user);
            return redirect('/');
        }
    }
}
