<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/export', [DashboardController::class, 'export'])->name('export');

Route::resource('todos', TodoController::class);
Route::get('/stats', [TodoController::class, 'stats'])->name('todos.stats');
