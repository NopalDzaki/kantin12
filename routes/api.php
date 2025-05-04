<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\StanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\DiskonController;
use App\Http\Controllers\TransaksiController;

// JWT Authentication
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('refresh', [AuthController::class, 'refresh']);

// CRUD for Siswa
Route::get('/getsiswa', [SiswaController::class, 'index']);
Route::get('/getsiswa/{id}', [SiswaController::class, 'show']);
Route::post('/addsiswa', [SiswaController::class, 'store']);
Route::put('/updatesiswa/{id}', [SiswaController::class, 'update']);
Route::delete('/deletesiswa/{id}', [SiswaController::class, 'destroy']);

// CRUD for Stan
Route::get('/getstan', [StanController::class, 'index']);
Route::get('/getstan/{id}', [StanController::class, 'show']);
Route::post('/addstan', [StanController::class, 'store']);
Route::put('/updatestan/{id}', [StanController::class, 'update']);
Route::delete('/deletestan/{id}', [StanController::class, 'destroy']);

// CRUD for Menu
Route::get('/getmenu', [MenuController::class, 'index']);
Route::get('/getmenu/{id}', [MenuController::class, 'show']);
Route::post('/addmenu', [MenuController::class, 'store']);
Route::put('/updatemenu/{id}', [MenuController::class, 'update']);
Route::delete('/deletemenu/{id}', [MenuController::class, 'destroy']);

// CRUD for Diskon
Route::get('/getdiskon', [DiskonController::class, 'index']);
Route::get('/getdiskon/{id}', [DiskonController::class, 'show']);
Route::post('/adddiskon', [DiskonController::class, 'store']);
Route::put('/updatediskon/{id}', [DiskonController::class, 'update']);
Route::delete('/deletediskon/{id}', [DiskonController::class, 'destroy']);

// CRUD for Transaksi
Route::get('/getorderall', [TransaksiController::class, 'getdetailall']);
Route::get('/getorder/{id}', [TransaksiController::class, 'getdetail']);
Route::post('/order', [TransaksiController::class, 'order']);
Route::post('/tambahitem/{id}', [TransaksiController::class, 'tambahitem']);
Route::put('/updateorder/{id}', [TransaksiController::class, 'updateStatus']);
Route::delete('/deleteorder/{id}', [TransaksiController::class, 'destroy']);
Route::get('/histori-bulan', [TransaksiController::class, 'getHistoriByBulan']); //untuk siswa
Route::get('/cetak-nota/{id}', [TransaksiController::class, 'cetakNota']);
Route::get('/pesanan-bulan', [TransaksiController::class, 'getPesananByBulan']); //untuk admin
Route::get('/rekap-pemasukan', [TransaksiController::class, 'getRekapPemasukan']); 
