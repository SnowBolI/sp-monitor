<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ApplicationController;

use App\Http\Controllers\AccountOfficerController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\AdminKasController;
use App\Http\Controllers\DireksiController;
use App\Http\Controllers\KepalaCabangController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KepalaBagianController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\FileController;

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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('home',[HomeController::class,'index'])->name('home');
Route::get('dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
Route::get('nasabah/edit/{no}', [HomeController::class, 'editNasabah'])->name('nasabah.edit');
Route::post('nasabah/update/{no}', [HomeController::class, 'updateNasabah'])->name('nasabah.update');
Route::post('nasabah', [HomeController::class, 'addNasabah'])->name('nasabah.store');
Route::delete('nasabah/delete/{no}', [HomeController::class, 'deleteNasabah'])->name('nasabah.delete');
Route::get('nasabah/detail/{no}', [HomeController::class, 'detailNasabah'])->name('nasabah.detail');
Route::post('nasabah/add', [HomeController::class, 'addNasabah'])->name('nasabah.add');
Route::get('/search', [HomeController::class, 'search'])->name('search');


Route::get('/', [AuthController::class, 'index'])->name('login');

Route::post('login', [AuthController::class, 'postLogin'])->name('login.post');
Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('register', [AuthController::class, 'postRegister'])->name('register.post');
Route::get('user/verify/{token}', [AuthController::class, 'verifyEmail'])->name('user.verify');
Route::get('page/error', [AuthController::class, 'showErrorPage'])->name('page.error');


Route::get('forget-password', [AuthController::class, 'forgetPasswordForm'])->name('forget.password');
Route::post('forget-password', [AuthController::class, 'forgetPasswordPost'])->name('forget.password.post');
Route::get('reset-password/{token}', [AuthController::class, 'resetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [AuthController::class, 'resetPasswordPost'])->name('reset.password.post');
Route::middleware('auth:web')->group(function(){
    
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
        Route::get('change-password', [AuthController::class, 'changePasswordForm'])->name('password.change.form');
        Route::post('change-password', [AuthController::class, 'changePasswordPost'])->name('password.change.post');
        
});

Route::prefix('direksi')
    ->name('direksi.')
    ->middleware('jabatan:1')
    ->group(function () {
        Route::get('/dashboard', [DireksiController::class, 'dashboard'])->name('dashboard');
        Route::get('/nasabah/edit/{no}', [DireksiController::class, 'editNasabah'])->name('nasabah.edit');
        Route::get('/admin-kas/search', [DireksiController::class, 'search'])->name('admin-kas.search');
        Route::put('/nasabah/update/{no}', [DireksiController::class, 'update'])->name('nasabah.update');
        Route::get('/direksi/search-nasabah', 'DireksiController@searchNasabah')->name('direksi.search-nasabah');
        Route::post('nasabah/add', [DireksiController::class, 'addNasabah'])->name('nasabah.add');
        Route::delete('nasabah/delete/{no}', [DireksiController::class, 'deleteNasabah'])->name('nasabah.delete');
        Route::post('/nasabah/surat', [DireksiController::class, 'addSurat'])->name('nasabah.surat');
        Route::get('/nasabah/cetak-pdf', [DireksiController::class, 'cetakPdf'])->name('nasabah.cetak-pdf');
});

Route::prefix('kepala-cabang')
    ->name('kepala-cabang.')
    ->middleware('jabatan:2')
    ->group(function () {
        Route::get('/dashboard', [KepalaCabangController::class, 'dashboard'])->name('dashboard');
        Route::get('/nasabah/edit/{no}', [KepalaCabangController::class, 'editNasabah'])->name('nasabah.edit');
        Route::get('/admin-kas/search', [KepalaCabangController::class, 'search'])->name('admin-kas.search');
        Route::put('/nasabah/update/{no}', [KepalaCabangController::class, 'update'])->name('nasabah.update');
        Route::post('nasabah/add', [KepalaCabangController::class, 'addNasabah'])->name('nasabah.add');
        Route::delete('nasabah/delete/{no}', [KepalaCabangController::class, 'deleteNasabah'])->name('nasabah.delete');
        Route::get('/nasabah/cetak-pdf', [KepalaCabangController::class, 'cetakPdf'])->name('nasabah.cetak-pdf');
        // Route::post('/nasabah/surat', [KepalaCabangController::class, 'addSurat'])->name('nasabah.surat');
});

// Routes for supervisor
Route::prefix('supervisor')
    ->name('supervisor.')
    ->middleware('jabatan:3')
    ->group(function () {
        Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');
        Route::get('/nasabah/edit/{no}', [SupervisorController::class, 'editNasabah'])->name('nasabah.edit');
        Route::get('/admin-kas/search', [SupervisorController::class, 'search'])->name('admin-kas.search');
        Route::put('/nasabah/update/{no}', [SupervisorController::class, 'update'])->name('nasabah.update');
        Route::post('nasabah/add', [SupervisorController::class, 'addNasabah'])->name('nasabah.add');
        Route::delete('nasabah/delete/{no}', [SupervisorController::class, 'deleteNasabah'])->name('nasabah.delete');
        Route::get('/nasabah/cetak-pdf', [SupervisorController::class, 'cetakPdf'])->name('nasabah.cetak-pdf');
});

Route::prefix('admin-kas')
    ->name('admin-kas.')
    ->middleware('jabatan:4')
    ->group(function () {
        Route::get('/dashboard', [AdminKasController::class, 'dashboard'])->name('dashboard');
        Route::get('/nasabah/edit/{no}', [AdminKasController::class, 'editNasabah'])->name('nasabah.edit');
        Route::get('/admin-kas/search', [AdminKasController::class, 'search'])->name('admin-kas.search');
        Route::put('/nasabah/update/{no}', [AdminKasController::class, 'update'])->name('nasabah.update');
        Route::post('nasabah/add', [AdminKasController::class, 'addNasabah'])->name('nasabah.add');
        Route::delete('nasabah/delete/{no}', [AdminKasController::class, 'deleteNasabah'])->name('nasabah.delete');
        Route::post('/nasabah/surat', [AdminKasController::class, 'addSurat'])->name('nasabah.surat');
        Route::post('/nasabah/import', [AdminKasController::class, 'importNasabah'])->name('nasabah.import');


    });

Route::prefix('account-officer')
    ->name('account-officer.')
    ->middleware('jabatan:5')
    ->group(function () {
        Route::get('/dashboard', [AccountOfficerController::class, 'dashboard'])->name('dashboard');
        Route::get('/nasabah/edit/{no}', [AccountOfficerController::class, 'editNasabah'])->name('nasabah.edit');
        Route::put('/nasabah/update/{no}', [AccountOfficerController::class, 'update'])->name('nasabah.update');
        Route::post('nasabah/add', [AccountOfficerController::class, 'addNasabah'])->name('nasabah.add');
        Route::delete('nasabah/delete/{id_peringatan}', [AccountOfficerController::class, 'deleteNasabah'])->name('nasabah.delete');
});

Route::prefix('kepala-bagian')
    ->name('kepala-bagian.')
    ->middleware('jabatan:6')
    ->group(function () {
        Route::get('/dashboard', [KepalaBagianController::class, 'dashboard'])->name('dashboard');
        Route::get('/nasabah/edit/{no}', [KepalaBagianController::class, 'editNasabah'])->name('nasabah.edit');
        Route::get('/admin-kas/search', [KepalaBagianController::class, 'search'])->name('admin-kas.search');
        Route::put('/nasabah/update/{no}', [KepalaBagianController::class, 'update'])->name('nasabah.update');
        Route::post('nasabah/add', [KepalaBagianController::class, 'addNasabah'])->name('nasabah.add');
        Route::delete('nasabah/delete/{no}', [KepalaBagianController::class, 'deleteNasabah'])->name('nasabah.delete');
        Route::get('/nasabah/cetak-pdf', [KepalaBagianController::class, 'cetakPdf'])->name('nasabah.cetak-pdf');
        // Route::post('/nasabah/surat', [KepalaCabangController::class, 'addSurat'])->name('nasabah.surat');
});

Route::prefix('super-admin')
    ->name('super-admin.')
    ->middleware('jabatan:99')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/cabang', [SuperAdminController::class, 'tampilkanCabang'])->name('cabang');
        Route::get('/key', [SuperAdminController::class, 'tampilkanKey'])->name('key');
        Route::delete('cabang/delete/{id_cabang}', [SuperAdminController::class, 'deleteCabang'])->name('cabang.delete');
        Route::get('/kantorkas', [SuperAdminController::class, 'tampilkanKantorKas'])->name('kantorkas');
        Route::delete('kantorkas/delete/{id_kantorkas}', [SuperAdminController::class, 'deleteKantorkas'])->name('kantorkas.delete');
        Route::get('/user/edit/{id}', [SuperAdminController::class, 'edit'])->name('user.edit');
        Route::post('cabang/add', [SuperAdminController::class, 'addCabang'])->name('cabang.add');
        Route::post('kantorkas/add', [SuperAdminController::class, 'addKantorkas'])->name('kantorkas.add');
        Route::post('key/add', [SuperAdminController::class, 'addKey'])->name('key.add');
        Route::delete('key/delete/{key}', [SuperAdminController::class, 'deleteKey'])->name('key.delete');
        Route::put('/user/update/{id}', [SuperAdminController::class, 'update'])->name('user.update'); 
        Route::post('/keys/import', [SuperAdminController::class, 'importKeys'])->name('keys.import');
        Route::get('/get-users-by-jabatan/{jabatanId}', 'SuperAdminController@getUsersByJabatan')->name('get-users-by-jabatan');
});


