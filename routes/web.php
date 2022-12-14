<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\TransactionController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect(route('budgets.index'));
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');

    Route::resource('transactions', TransactionController::class)->except('show');
    Route::resource('budgets', BudgetController::class)->except('show');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
});

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::put('/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/{product}', [CartController::class, 'destroy'])->name('cart.item.destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('cart.clear');
});

Route::prefix('checkout')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/status', [CheckoutController::class, 'status'])->name('checkout.status');
});

Route::prefix('/orders')->group(function () {
    Route::post('/', [OrderController::class, 'store'])->name('orders.store');
});

Route::prefix('/download')->group(function () {
    Route::get('terms-and-conditions', [DownloadController::class, 'termsAndConditions'])->name('download.terms.and.conditions');
});

require __DIR__ . '/auth.php';