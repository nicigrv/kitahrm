<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KitaController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\TrainingCategoryController;
use App\Http\Controllers\TrainingCompletionController;
use App\Http\Controllers\UserManagementController;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Root redirect
Route::get('/', function () {
    return redirect('/dashboard');
});

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Kitas
    Route::get('/kitas', [KitaController::class, 'index'])->name('kitas.index');
    Route::get('/kitas/create', [KitaController::class, 'create'])->name('kitas.create')->middleware('role:ADMIN');
    Route::post('/kitas', [KitaController::class, 'store'])->name('kitas.store')->middleware('role:ADMIN');
    Route::get('/kitas/{kita}', [KitaController::class, 'show'])->name('kitas.show');
    Route::get('/kitas/{kita}/edit', [KitaController::class, 'edit'])->name('kitas.edit')->middleware('role:ADMIN');
    Route::put('/kitas/{kita}', [KitaController::class, 'update'])->name('kitas.update')->middleware('role:ADMIN');
    Route::delete('/kitas/{kita}', [KitaController::class, 'destroy'])->name('kitas.destroy')->middleware('role:ADMIN');

    // User management (Admin only)
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index')->middleware('role:ADMIN');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create')->middleware('role:ADMIN');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store')->middleware('role:ADMIN');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit')->middleware('role:ADMIN');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update')->middleware('role:ADMIN');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy')->middleware('role:ADMIN');

    // Employees
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create')->middleware('role:ADMIN,KITA_MANAGER');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store')->middleware('role:ADMIN,KITA_MANAGER');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit')->middleware('role:ADMIN,KITA_MANAGER');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update')->middleware('role:ADMIN,KITA_MANAGER');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('role:ADMIN,KITA_MANAGER');

    // Employee Documents
    Route::get('/employees/{employee}/documents', [DocumentController::class, 'index'])->name('employees.documents.index');
    Route::post('/employees/{employee}/documents', [DocumentController::class, 'store'])->name('employees.documents.store')->middleware('role:ADMIN,KITA_MANAGER');
    Route::get('/employees/{employee}/documents/{document}/download', [DocumentController::class, 'download'])->name('employees.documents.download');
    Route::delete('/employees/{employee}/documents/{document}', [DocumentController::class, 'destroy'])->name('employees.documents.destroy')->middleware('role:ADMIN,KITA_MANAGER');

    // Training
    Route::get('/training', [TrainingCompletionController::class, 'matrix'])->name('training.matrix');

    // Training Categories (admin only)
    Route::get('/training/categories', [TrainingCategoryController::class, 'index'])->name('training.categories.index')->middleware('role:ADMIN');
    Route::post('/training/categories', [TrainingCategoryController::class, 'store'])->name('training.categories.store')->middleware('role:ADMIN');
    Route::put('/training/categories/{category}', [TrainingCategoryController::class, 'update'])->name('training.categories.update')->middleware('role:ADMIN');
    Route::delete('/training/categories/{category}', [TrainingCategoryController::class, 'destroy'])->name('training.categories.destroy')->middleware('role:ADMIN');

    // Training Completions
    Route::get('/employees/{employee}/training', [TrainingCompletionController::class, 'index'])->name('employees.training.index');
    Route::get('/employees/{employee}/training/create', [TrainingCompletionController::class, 'create'])->name('employees.training.create')->middleware('role:ADMIN,KITA_MANAGER');
    Route::post('/training/completions', [TrainingCompletionController::class, 'store'])->name('training.completions.store')->middleware('role:ADMIN,KITA_MANAGER');
    Route::get('/training/completions/{completion}/edit', [TrainingCompletionController::class, 'edit'])->name('training.completions.edit')->middleware('role:ADMIN,KITA_MANAGER');
    Route::put('/training/completions/{completion}', [TrainingCompletionController::class, 'update'])->name('training.completions.update')->middleware('role:ADMIN,KITA_MANAGER');
    Route::delete('/training/completions/{completion}', [TrainingCompletionController::class, 'destroy'])->name('training.completions.destroy')->middleware('role:ADMIN,KITA_MANAGER');
});
