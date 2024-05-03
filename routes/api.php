<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AdController;
use App\Http\Controllers\Api\V1\LikeController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\SplashAdController;
use App\Http\Controllers\Api\V1\FavouriteController;
use App\Http\Controllers\Api\V1\BannerPriceController;
use App\Http\Controllers\Api\V1\SubcategoryController;
use App\Http\Controllers\Api\V1\PromotionPlanController;
use App\Http\Controllers\Api\V1\SplashAdPriceController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('', function () {
    \Illuminate\Support\Facades\Artisan::call('config:cache');
});

Route::group(['prefix' => 'auth/'], function () {
    Route::post('register', [\App\Http\Controllers\Api\V1\AuthController::class, 'registration']);
    Route::post('login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::get('logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/fcm-token', [\App\Http\Controllers\Api\V1\AuthController::class, 'fcmToken'])->middleware('auth:sanctum');
});

Route::get('user', [\App\Http\Controllers\Api\V1\UserController::class, 'show'])
->middleware('auth:sanctum');

Route::group(['prefix' => 'users'], function () {
    Route::get('', [\App\Http\Controllers\Api\V1\UserController::class, 'index'])
        ->middleware(['auth:sanctum', 'is.admin']);
    Route::get('{user}', [\App\Http\Controllers\Api\V1\UserController::class, 'show'])
        ->middleware('auth:sanctum');
    Route::patch('', [\App\Http\Controllers\Api\V1\UserController::class, 'update'])
        ->middleware('auth:sanctum');
    Route::delete('', [\App\Http\Controllers\Api\V1\UserController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'is.admin']);
});
Route::patch('admins', [\App\Http\Controllers\Api\V1\AdminController::class, 'update']);
Route::delete('admins', [\App\Http\Controllers\Api\V1\AdminController::class, 'destroy']);
Route::get('admin', [\App\Http\Controllers\Api\V1\AdminController::class, 'show']);
Route::resource('admins', \App\Http\Controllers\Api\V1\AdminController::class)
    ->middleware(['auth:sanctum', 'is.admin'])->only(['index', 'store']);
Route::post('admins/profile', [\App\Http\Controllers\Api\V1\AdminController::class, 'uploadProfile'])
    ->middleware(['auth:sanctum', 'is.admin']);
Route::patch('roles', [\App\Http\Controllers\Api\V1\RoleController::class, 'update'])->middleware(['auth:sanctum', 'is.admin']);
Route::delete('roles', [\App\Http\Controllers\Api\V1\RoleController::class, 'destroy'])->middleware(['auth:sanctum', 'is.admin']);
Route::get('role', [\App\Http\Controllers\Api\V1\RoleController::class, 'show'])->middleware(['auth:sanctum', 'is.admin']);
Route::resource('roles', \App\Http\Controllers\Api\V1\RoleController::class)->only(['index', 'store'])
    ->middleware(['auth:sanctum', 'is.admin']);


    Route::get('client', [\App\Http\Controllers\Api\V1\ClientController::class, 'show'])
    ->middleware('auth:sanctum');
Route::group(['prefix' => 'clients'], function () {
    Route::get('', [\App\Http\Controllers\Api\V1\ClientController::class, 'index'])
        ->middleware(['auth:sanctum', 'is.admin']);

    Route::patch('', [\App\Http\Controllers\Api\V1\ClientController::class, 'update'])
        ->middleware('auth:sanctum');
    Route::post('/profile', [\App\Http\Controllers\Api\V1\ClientController::class, 'uploadProfile'])
        ->middleware('auth:sanctum');
    Route::delete('', [\App\Http\Controllers\Api\V1\ClientController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'is.admin']);
});

Route::get('company', [\App\Http\Controllers\Api\V1\CompanyController::class, 'show'])
->middleware('auth:sanctum');
Route::group(['prefix' => 'companies'], function () {
    Route::get('', [\App\Http\Controllers\Api\V1\CompanyController::class, 'index'])
        ->middleware(['auth:sanctum']);

    Route::post('', [\App\Http\Controllers\Api\V1\CompanyController::class, 'store'])
        ->middleware('auth:sanctum');
    Route::patch('', [\App\Http\Controllers\Api\V1\CompanyController::class, 'update'])
        ->middleware('auth:sanctum');
    Route::post('{company}/profile', [\App\Http\Controllers\Api\V1\CompanyController::class, 'uploadProfile'])
        ->middleware('auth:sanctum');
    Route::delete('', [\App\Http\Controllers\Api\V1\CompanyController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'is.admin']);
});

Route::group(['prefix' => 'notifications'], function () {
    Route::resource('', \App\Http\Controllers\Api\V1\NotificationController::class)
        ->middleware(['auth:sanctum', 'is.admin'])->only(['store']);
});


Route::post('messages', [\App\Http\Controllers\Api\V1\MessageController::class, 'store'])
    ->middleware(['auth:sanctum']);


Route::post('ad', [App\Http\Controllers\Api\V1\AdController::class, 'store'])->middleware(['auth:sanctum']);

Route::patch('/ad', [App\Http\Controllers\Api\V1\AdController::class, 'update'])->middleware(['auth:sanctum']);
Route::delete('/ad', [App\Http\Controllers\Api\V1\AdController::class, 'destroy'])->middleware(['auth:sanctum']);
Route::post('/ad/promote', [\App\Http\Controllers\Api\V1\AdController::class, 'promote'])->middleware(['auth:sanctum']);
Route::get('/ad/end-promotion', [\App\Http\Controllers\Api\V1\AdController::class, 'endPromotion'])->middleware(['auth:sanctum']);


Route::patch('promotion_plan', App\Http\Controllers\Api\V1\PlanController::class)->middleware(['auth:sanctum', 'is.admin']);
Route::patch('splash-ad', [\App\Http\Controllers\Api\V1\SplashAdController::class, 'update'])->middleware(['auth:sanctum', 'is.admin']);
Route::delete('splash-ad', [\App\Http\Controllers\Api\V1\SplashAdController::class, 'destroy'])->middleware(['auth:sanctum', 'is.admin']);
Route::apiResource('splash-ad', \App\Http\Controllers\Api\V1\SplashAdController::class)->middleware(['auth:sanctum', 'is.admin'])->only(['store']);
Route::post('/comment', [App\Http\Controllers\Api\V1\CommentController::class, 'store'])->middleware(['auth:sanctum']);
Route::delete('/comment', [App\Http\Controllers\Api\V1\CommentController::class, 'destroy'])->middleware(['auth:sanctum']);
Route::post('/like', [App\Http\Controllers\Api\V1\LikeController::class, 'likeAd'])->middleware(['auth:sanctum']);
Route::delete('/unlike', [App\Http\Controllers\Api\V1\LikeController::class, 'unlikeAd'])->middleware(['auth:sanctum']);
Route::patch('banner', [App\Http\Controllers\Api\V1\BannerController::class, 'update'])->middleware(['auth:sanctum']);
Route::delete('banner', [App\Http\Controllers\Api\V1\BannerController::class, 'destroy'])->middleware(['auth:sanctum']);
Route::apiResource('banner', App\Http\Controllers\Api\V1\BannerController::class)->middleware(['auth:sanctum'])->only(['store']);
Route::patch('banner-pricing', [App\Http\Controllers\Api\V1\BannerPriceController::class, 'update'])->middleware(['auth:sanctum']);

Route::patch('splash-ad-pricing', \App\Http\Controllers\Api\V1\SplashAdPriceController::class)->middleware(['auth:sanctum', 'is.admin']);


Route::post('ad/favourite',[App\Http\Controllers\Api\V1\FavouriteController::class,'store'])->middleware(['auth:sanctum']);

Route::delete('ad/unfavourite',[App\Http\Controllers\Api\V1\FavouriteController::class,'destroy'])->middleware(['auth:sanctum']);


// Route::prefix('V1')->group(function () {

    //categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('category', [CategoryController::class, 'show']);
    Route::get('subcategories', [SubcategoryController::class, 'index']);
    Route::get('subcategory', [SubcategoryController::class, 'show']);
    Route::post('subcategory/create', [SubcategoryController::class, 'store']);
    Route::post('subcategory/update', [SubcategoryController::class, 'update']);
    Route::post('subcategory/delete', [SubcategoryController::class, 'destroy']);
    //ads
    Route::get('/ad', [AdController::class, 'index']);
    Route::get('/ad/show', [AdController::class, 'show']);
    Route::get('/ad/types', [AdController::class, 'getTypes']);
    Route::get('/ad/pending', [AdController::class, 'getPending']);
    Route::get('/ad/category', [AdController::class, 'byCategory']);
    Route::get('/ad/subcategory', [AdController::class, 'bySubCategory']);
    Route::get('/ad/company', [AdController::class, 'byCompany']);
    Route::post('/ad/filter', [AdController::class, 'filterByAttributes']);
    Route::get('/ad/search', [AdController::class, 'searchAd']);
    Route::get('/user/ads', [AdController::class, 'userAds']);


    Route::get('/user/comments', [CommentController::class, 'userComments']);
    Route::get('/user/likes', [LikeController::class, 'userLikes']);
    Route::get('/user/favourites', [FavouriteController::class, 'userFavourites']);
    Route::get('/user/prometedads', [AdController::class, 'prometedUserAds']);
    //banners
    Route::get('banners', [BannerController::class, 'index']);
    Route::get('banner', [BannerController::class, 'show']);



    //promotion plans
    Route::get('promotion_plans', [PromotionPlanController::class, 'index']);
    Route::get('promotion_plan', [PromotionPlanController::class, 'show']);

    // banner pricing
    Route::get('banner-pricings', [BannerPriceController::class, 'index']);
    Route::get('banner-pricing', [BannerPriceController::class, 'show']);

    // splash ad pricing
    Route::get('splash-ad-pricings', [SplashAdPriceController::class, 'index']);
    Route::get('splash-ad-pricing', [SplashAdPriceController::class, 'show']);

    //splash ads
    Route::get('/splash-ad/random', [SplashAdController::class, 'randomIndex']);
    Route::get('splash-ads', [SplashAdController::class, 'index']);
    Route::get('splash-ad', [SplashAdController::class, 'show']);
// });
