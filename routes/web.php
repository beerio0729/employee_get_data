<?php

use App\Events\ProcessEmpDocEvent;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;


Route::fallback(function () {
    return redirect('/');
});

Route::get('/test', function () {
    $user = auth()->user(); // user 
    event(new ProcessEmpDocEvent('Hello from server!', $user));
    return 'Event sent';
});

Route::get('pdf', [PDFController::class, 'pdf'])->middleware('auth');