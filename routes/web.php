<?php

use App\Events\ProcessEmpDocEvent;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::fallback(function () {
    return redirect('/');
});

// Route::get('/test', function () {
//     $user = auth()->user(); // user 
//     event(new ProcessEmpDocEvent('Hello from server!', $user));
//     return 'Event sent';
// });


Route::get('/test', function () {
    event(new ProcessEmpDocEvent('กำลังเตรียมข้อมูล...', auth()->user()));
    return view('test');
});

