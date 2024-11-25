<?php

use App\Http\Controllers\MobileAccountController;
use App\Http\Controllers\MobileAdminController;
use App\Http\Controllers\MobileLoginController;
use App\Http\Controllers\MobileMonitoringController;
use App\Http\Controllers\MobileRegisterController;
use App\Http\Controllers\MobileSuratPeringatanController;
use App\Http\Controllers\MobileKunjunganController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

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
//register
Route::get('checkconnection', [MobileRegisterController::class, 'checkConnection']);
Route::get('cabang',[MobileMonitoringController::class, 'cabang']);

Route::post('registermobile', [MobileRegisterController::class, 'register']);

//login
Route::post('loginmobile',[MobileLoginController::class, 'login']);

Route::middleware(['auth.with.api.token:Direksi,Kepala Cabang,Kepala Bagian,Supervisor,Admin Kas,Account Officer,Super Admin'])->group(function () {
    Route::get('usermobile',[MobileAccountController::class, 'getUserDetails']);
    Route::post('logoutmobile', [MobileAccountController::class, 'logout']);
    //suratperingatan
    Route::get('nasabah', [MobileSuratPeringatanController::class, 'getNasabahSP']);
    // Route::post('surat_peringatan', [MobileSuratPeringatanController::class, 'SuratPeringatan']);
    Route::post('surat_peringatan/update', [MobileSuratPeringatanController::class, 'updateSuratPeringatan']);
    Route::get('surat-peringatan/gambar/{filename}', [MobileSuratPeringatanController::class, 'serveImage']);
    Route::get('surat-peringatan/pdf/{filename}', [MobileSuratPeringatanController::class ,'servePdf']);

    //account



    //monitoring
    Route::get('nasabahs', [MobileMonitoringController::class, 'getNasabah']);
    Route::get('cabang',[MobileMonitoringController::class, 'cabang']);


    //admin
    Route::get('alldata', [MobileAdminController::class, 'getAllData']);
    Route::put('user/update/{id}', [MobileAdminController::class, 'updateUser']);
    Route::get('usermobileadmin',[MobileAdminController::class, 'getUserAdmin']);
    
        //kunjungan
    Route::get('kunjungan/get/{no_nasabah}', [MobileKunjunganController::class, 'getKunjunganList']);
    Route::get('kunjungan/gambar/{filename}', [MobileKunjunganController::class, 'serveImage']);
    Route::post('kunjungan/tambah', [MobileKunjunganController::class, 'tambahKunjungan']);
    Route::get('kunjungan/list', [MobileKunjunganController::class, 'getNasabahKunjungan']);


});