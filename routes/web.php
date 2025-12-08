<?php

use App\Events\ProcessEmpDocEvent;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\Auth\SocialAuthController;


Route::fallback(function () {
    return redirect('/');
});

Route::get('/test', function () {
    $user = auth()->user(); // user 
    event(new ProcessEmpDocEvent('Hello from server!', $user));
    return 'Event sent';
});

Route::get('pdf', [PDFController::class, 'pdf'])->middleware('auth');

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect']);
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback']);

Route::get('/wa-test', [WhatsAppController::class, 'send']);