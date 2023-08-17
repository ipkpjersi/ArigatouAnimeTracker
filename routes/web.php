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

Route::get('/users/{username}', [UserController::class, 'detail'])->name("users.detail");

Route::get('/anime/', [AnimeController::class, 'list'])->name("anime.list");

Route::get('/anime/getAnimeData', [AnimeController::class, 'getAnimeData'])->name("anime.data");

Route::get('/anime/{id}/{title?}', [AnimeController::class, 'detail'])->name('anime.detail');

Route::get('/animelist/{username}', [AnimeController::class, 'userAnimeList'])->name('user.anime.list');

Route::get('/animelist-v2/{username}',  [AnimeController::class, 'userAnimeListV2'])->name('user.anime.list.v2');
Route::get('/animelist-v2/data/{username}', [AnimeController::class, 'getUserAnimeData'])->name('user.anime.list.data.v2');

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

    Route::post('/anime/{id}/add-to-list/{redirect?}', [AnimeController::class, 'addToList'])->name('anime.addToList');

    Route::delete('/anime/{id}/delete-from-list/{redirect?}', [AnimeController::class, 'removeFromList'])->name('anime.deleteFromList');

    Route::post('/users/{userId}/ban', [UserController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{userId}/unban', [UserController::class, 'unbanUser'])->name('users.unban');
    Route::post('/users/{userId}/remove-avatar', [UserController::class, 'removeAvatar'])->name('users.removeAvatar');

    Route::post('/animelist/{username}/update', [AnimeController::class, 'updateUserAnimeList'])->name('user.anime.update');

    Route::post('/animelist-v2/{username}/update', [AnimeController::class, 'updateUserAnimeListV2'])->name('user.anime.update.v2');
});

require __DIR__.'/auth.php';
