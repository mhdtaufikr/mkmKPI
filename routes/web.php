<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MstSectionController;
use App\Http\Controllers\MstShopController;
use App\Http\Controllers\MstModelController;
use App\Http\Controllers\MstDowntimeController;
use App\Http\Controllers\ChecksheetController;



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

Route::get('/', [AuthController::class, 'login'])->name('login');
Route::post('/auth/login', [AuthController::class, 'postLogin']);
Route::get('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth'])->group(function () {
    //Home Controller
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    //Checksheet Controller
    Route::get('/checksheet', [ChecksheetController::class, 'index'])->middleware(['checkRole:IT']);
    Route::post('/checksheet/store/main', [ChecksheetController::class, 'storeMain'])->middleware(['checkRole:IT']);
    Route::get('/checksheet/form/{id}', [ChecksheetController::class, 'formChecksheet'])->middleware(['checkRole:IT'])->name('form.checksheet');
    Route::post('/checksheet/detail/store', [ChecksheetController::class, 'storeForm'])->middleware(['checkRole:IT']);
    Route::get('/checksheet/detail/{id}', [ChecksheetController::class, 'showDetail'])->middleware(['checkRole:IT']);
    Route::get('/checksheet/update/{id}', [ChecksheetController::class, 'updateDetail'])->middleware(['checkRole:IT']);
    Route::post('/checksheet/detail/update', [ChecksheetController::class, 'updateForm'])->middleware(['checkRole:IT']);
    Route::post('/checksheet/export', [ChecksheetController::class, 'exportExcel'])->middleware(['checkRole:IT']);

    //Dropdown Controller
     Route::get('/dropdown', [DropdownController::class, 'index'])->middleware(['checkRole:IT']);
     Route::post('/dropdown/store', [DropdownController::class, 'store'])->middleware(['checkRole:IT']);
     Route::patch('/dropdown/update/{id}', [DropdownController::class, 'update'])->middleware(['checkRole:IT']);
     Route::delete('/dropdown/delete/{id}', [DropdownController::class, 'delete'])->middleware(['checkRole:IT']);

     //Rules Controller
     Route::get('/rule', [RulesController::class, 'index'])->middleware(['checkRole:IT']);
     Route::post('/rule/store', [RulesController::class, 'store'])->middleware(['checkRole:IT']);
     Route::patch('/rule/update/{id}', [RulesController::class, 'update'])->middleware(['checkRole:IT']);
     Route::delete('/rule/delete/{id}', [RulesController::class, 'delete'])->middleware(['checkRole:IT']);

     //User Controller
     Route::get('/user', [UserController::class, 'index'])->middleware(['checkRole:IT']);
     Route::post('/user/store', [UserController::class, 'store'])->middleware(['checkRole:IT']);
     Route::post('/user/store-partner', [UserController::class, 'storePartner'])->middleware(['checkRole:IT']);
     Route::patch('/user/update/{user}', [UserController::class, 'update'])->middleware(['checkRole:IT']);
     Route::get('/user/revoke/{user}', [UserController::class, 'revoke'])->middleware(['checkRole:IT']);
     Route::get('/user/access/{user}', [UserController::class, 'access'])->middleware(['checkRole:IT']);

     //MstSection Controller
     Route::get('/mst/section', [MstSectionController::class, 'index'])->middleware(['checkRole:IT']);
     Route::post('/mst/section/store', [MstSectionController::class, 'store'])->middleware(['checkRole:IT']);
     Route::patch('/mst/section/update', [MstSectionController::class, 'update'])->middleware(['checkRole:IT']);

     //MstShopController Controller
     Route::get('/mst/shop', [MstShopController::class, 'index'])->middleware(['checkRole:IT']);
     Route::post('/mst/shop/store', [MstShopController::class, 'store'])->middleware(['checkRole:IT']);
     Route::patch('/mst/shop/update', [MstShopController::class, 'update'])->middleware(['checkRole:IT']);

     //MstModelController Controller
     Route::get('/mst/model', [MstModelController::class, 'index'])->middleware(['checkRole:IT']);
     Route::post('/mst/model/store', [MstModelController::class, 'store'])->middleware(['checkRole:IT']);
     Route::patch('/mst/model/update', [MstModelController::class, 'update'])->middleware(['checkRole:IT']);

    //MstDowntimeController Controller
    Route::get('/mst/downtime', [MstDowntimeController::class, 'index'])->middleware(['checkRole:IT']);
    Route::post('/mst/downtime/store', [MstDowntimeController::class, 'store'])->middleware(['checkRole:IT']);
    Route::patch('/mst/downtime/update', [MstDowntimeController::class, 'update'])->middleware(['checkRole:IT']);


});
