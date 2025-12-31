<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Customers
Route::post('/customers', [CustomerController::class, 'create']);
Route::post('/customers/{customer}/accounts', [CustomerController::class, 'createAccount']);
Route::patch('/customers/{customer}/block', [CustomerController::class, 'block']);
Route::patch('/customers/{customer}/close', [CustomerController::class, 'close']);

// Accounts
Route::get('/accounts', [AccountController::class, 'index']);
Route::post('/accounts/{account}/deposit', [AccountController::class, 'deposit']);
Route::post('/accounts/{account}/withdraw', [AccountController::class, 'withdraw']);
Route::post('/accounts/{account}/transfer', [AccountController::class, 'transfer']);

Route::patch('/accounts/{account}/activate', [AccountController::class, 'activate']);
Route::patch('/accounts/{account}/block', [AccountController::class, 'block']);
Route::patch('/accounts/{account}/close', [AccountController::class, 'close']);

// Transactions
Route::get('/transactions', [TransactionController::class, 'index']);