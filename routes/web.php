<?php

use App\Http\Controllers\AnimeController;
use App\Http\Controllers\InviteCodeController;
use App\Http\Controllers\PasswordSecurityController;
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

Route::get('/users/', [UserController::class, 'list'])->name("users.list")->middleware('2fa');

Route::get('/users/getUserData', [UserController::class, 'getUserData'])->name('users.data')->middleware('2fa');

Route::get('/users/{username}', [UserController::class, 'detail'])->name("users.detail")->middleware('2fa');

Route::get('/anime/', [AnimeController::class, 'list'])->name("anime.list")->middleware('2fa');

Route::get('/anime/getAnimeData', [AnimeController::class, 'getAnimeData'])->name("anime.data")->middleware('2fa');

Route::get('/anime/{id}/{title?}', [AnimeController::class, 'detail'])->name('anime.detail')->middleware('2fa');

Route::get('/animelist/{username}', [AnimeController::class, 'userAnimeList'])->name('user.anime.list')->middleware('2fa');

Route::get('/animelist-v2/{username}',  [AnimeController::class, 'userAnimeListV2'])->name('user.anime.list.v2')->middleware('2fa');
Route::get('/animelist-v2/data/{username}', [AnimeController::class, 'getUserAnimeDataV2'])->name('user.anime.list.data.v2')->middleware('2fa');

Route::get('/top-anime', [AnimeController::class, 'topAnime'])->name('anime.top')->middleware('2fa');

Route::get('/categories', [AnimeController::class, 'categories'])->name('anime.categories')->middleware('2fa');

Route::get('/category/{category}/{view?}', [AnimeController::class, 'category'])->name('anime.category')->middleware('2fa');

//Protected routes
Route::middleware('auth', '2fa')->group(function () {
    Route::get('/2fa', [PasswordSecurityController::class, 'show2faForm'])->name('2fa');
    Route::post('/generate2faSecret', [PasswordSecurityController::class, 'generate2faSecret'])->name('generate2faSecret');
    Route::post('/2fa', [PasswordSecurityController::class, 'enable2fa'])->name('enable2fa');
    Route::post('/disable2fa', [PasswordSecurityController::class, 'disable2fa'])->name('disable2fa');
    Route::match(['get', 'post'], '/2faVerify', function () {
        return redirect(str_contains(URL()->previous(), '2faVerify') ? '/' : URL()->previous());
    })->name('2faVerify')->middleware('2fa');
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

    Route::post('/user/{userId}/delete-avatar', [UserController::class, 'deleteAvatar'])->name('avatar.delete');

    Route::post('/animelist/{username}/update/{redirectBack?}', [AnimeController::class, 'updateUserAnimeList'])->name('user.anime.update');

    Route::post('/animelist/{username}/update-anime-status', [AnimeController::class, 'updateAnimeStatus']);

    Route::post('/animelist-v2/{username}/update', [AnimeController::class, 'updateUserAnimeListV2'])->name('user.anime.update.v2');

    Route::get('/import/animelist', [AnimeController::class, 'importAnimeListView'])->name('import.animelist');

    Route::post('/import/animelist', [AnimeController::class, 'importAnimeList'])->name('import.animelistdata');

    Route::get('/export/animelist', [AnimeController::class, 'exportAnimeListView'])->name('export.animelist');

    Route::post('/export/animelist', [AnimeController::class, 'exportAnimeList'])->name('export.animelistdata');

    Route::post('/animelist/{username}/clear',  [AnimeController::class, 'clearAnimeList'])->name('user.anime.clear');

    Route::post('/anime/{username}/clearSortOrders', [AnimeController::class, 'clearAnimeListSortOrders'])->name('user.anime.clearSortOrders');

    Route::post('/add-friend/{friendId}', [UserController::class, 'addFriend'])->name('add-friend');
    Route::post('/remove-friend/{friendId}', [UserController::class, 'removeFriend'])->name('remove-friend');

    Route::post('/anime/add-review', [AnimeController::class, 'addReview'])->name('anime.addReview');
    Route::put('/anime/{id}/update-review', [AnimeController::class, 'updateReview'])->name('anime.updateReview');
    Route::delete('/anime/{id}/delete-review', [AnimeController::class, 'deleteReview'])->name('anime.deleteReview');

    Route::post('/toggle-friend-publicly/{id}', [UserController::class, 'toggleFriendPublicly'])->name('toggle-friend-publicly');

    Route::post('/invite-codes/generate-invite-codes', [InviteCodeController::class, 'generateInviteCodes'])->name('generate-invite-codes');
    Route::post('/invite-codes/revoke-unused-invite-codes', [InviteCodeController::class, 'revokeUnusedInviteCodes'])->name('revoke-unused-invite-codes');
    Route::get('/invite-codes/list', [InviteCodeController::class, 'index'])->name('invite-codes-index');
    Route::get('/invite-codes/data', [InviteCodeController::class, 'data'])->name('invite-codes-data');

});

require __DIR__.'/auth.php';
