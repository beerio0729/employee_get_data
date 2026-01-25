<?php

namespace App\Http\Controllers\Auth;


use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Events\ProcessEmpDocEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\LineSendMessageService;
use Laravel\Socialite\Facades\Socialite;
use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;


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

            $user = User::create([
                'name'        => $socialUser->getName() ?? $socialUser->getNickname(),
                'email'       => $socialUser->getEmail(),
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
                'password'    => bcrypt(Str::random(16)),
                'role_id'     => 3,
            ]);
            $workStatus = $user->userHasoneWorkStatus()->create([
                'work_status_def_detail_id' => WorkStatusDefinationDetail::statusId('new_applicant'),
            ]);

            $workStatus->workStatusHasonePreEmp()->create([
                'applied_at' => now()->locale('th'),
            ]);

            $user->userHasoneHistory()->create([
                'data' => [[
                    'event' => 'applied',
                    'description' => 'สมัครครั้งแรกสำเร็จ',
                    'date' => Carbon::now()->format('Y-m-d H:i:s'),
                ]],
            ]);
            Auth::login($user);
            LineSendMessageService::send($socialUser->getId(), ['ยินดีต้อนรับสู่เว็บอับโหลดเรซูเม่', 'กรุณาอับเดตข้อมูลโปรไฟล์ให้ครบถ้วน']);
            return redirect('/profile');
        } else {
            Auth::login($user);
            return redirect('/');
        }
    }
}
