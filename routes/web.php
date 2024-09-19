<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Presensi;

Route::post('/login',[AuthController::class,'login'])->name('login');
Route::group(['middleware' => 'auth'],function(){
    Route::get('presensi',Presensi::class)->name('presensi');
});

// Route::get('/login',function(){
//     return redirect('admin/login');
// })->name('login');

Route::get('/', function () {
    return view('welcome');
});
