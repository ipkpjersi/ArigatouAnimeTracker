<?php

use App\Http\Controllers\AnimeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//Unprotected routes
Route::get('/', function () {
    if (Auth::user() != null) {
        return redirect('home');
    }
    return view('welcome');
})->name("welcome");

Route::get('/users/', [UserController::class, 'list'])->name("users.list");

Route::get('/user/getUserData', [UserController::class, 'getUserData'])->name('users.data');

Route::get('/users/{id}/{name?}', [UserController::class, 'detail'])->name("users.detail");

Route::get('/anime/', [AnimeController::class, 'list'])->name("anime.list");

Route::get('/anime/getAnimeData', [AnimeController::class, 'getAnimeData'])->name("anime.data");

Route::get('/anime/{id}/{title?}', [AnimeController::class, 'detail'])->name('anime.detail');


//Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/home', function () {
        return view('dashboard');
    })->name('home');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
