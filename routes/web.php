<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AppHomeController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\FallbackRouteController;
use App\Http\Controllers\SpotifyImportController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\TrackController as TrackControllerAlias;
use App\Http\Controllers\UserProfileController;
use Common\Channels\ChannelController;
use Common\Core\Controllers\HomeController;

Route::get('/', AppHomeController::class);
Route::get('artist/{artist}', [ArtistController::class, 'show']);
Route::get('artist/{artist}/{name}', [ArtistController::class, 'show']);
Route::get('album/{album}/{artistName}/{albumName}', [AlbumController::class, 'show']);
Route::get('track/{track}', [TrackControllerAlias::class, 'show']);
Route::get('track/{track}/{name}', [TrackControllerAlias::class, 'show']);
Route::get('track/{track}/{name}/embed', [TrackControllerAlias::class, 'show']);
Route::get('playlist/{id}', [PlaylistController::class, 'show']);
Route::get('playlist/{id}/{name}', [PlaylistController::class, 'show']);
Route::get('user/{user}/{name}', [UserProfileController::class, 'show']);
Route::get('user/{user}/{name}/{tab}', [UserProfileController::class, 'show']);
Route::get('search/{query}', [SearchController::class, 'index']);
Route::get('search/{query}/{tab}', [SearchController::class, 'index']);
Route::get('channels/{channel}', [ChannelController::class, 'show']);
Route::get('channel/{channel}', [ChannelController::class, 'show']);

Route::get('blog', [BlogPostController::class, 'index']);
Route::get('blog/category/{blogCategory}', [BlogCategoryController::class, 'show']);
Route::get('blog/{blogPost}', [BlogPostController::class, 'show']);

Route::middleware('auth')->prefix('spotify/import')->name('spotify.import.')->group(function () {
    Route::get('authorize', [SpotifyImportController::class, 'start'])->name('authorize');
    Route::get('callback', [SpotifyImportController::class, 'callback'])->name('callback');
});

Route::get('contact', [HomeController::class, 'render']);
Route::get('login', [HomeController::class, 'render'])->name('login');
Route::get('register', [HomeController::class, 'render'])->name('register');
Route::get('forgot-password', [HomeController::class, 'render']);
Route::get('pricing', '\Common\Billing\PricingPageController');

// CHANNELS and fallback to client rendering if no channel matches
Route::fallback(FallbackRouteController::class);

Route::get('/app-ads.txt', function () {
    return response()->file(public_path('app-ads.txt'), [
        'Content-Type' => 'text/plain'
    ]);
});


Route::get('/app-ads.txt', function () {
    return redirect('/public/app-ads.txt');
});

Route::get('/ads.txt', function () {
    return response()->file(public_path('ads.txt'), [
        'Content-Type' => 'text/plain'
    ]);
});


Route::get('/ads.txt', function () {
    return redirect('/public/ads.txt');
});