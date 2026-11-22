<?php

use App\Http\Controllers\LaptopController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\LaptopController as AdminLaptopController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SellerProfileController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

// ---------- Registered users ----------
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', [LaptopController::class, 'index'])->name('home');
    Route::get('/laptops', [LaptopController::class, 'index'])->name('laptops.index');
    Route::get('/dashboard', fn () => redirect()->route('laptops.index'))->name('dashboard');

    Route::post('/laptops/{laptop}/wishlist', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::post('/laptops/{laptop}/orders', [OrderController::class, 'store'])->middleware('throttle:10,1')->name('orders.store');
    Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('orders.my');
    Route::get('/incoming-orders', [OrderController::class, 'incoming'])->name('orders.incoming');
    Route::get('/orders/{order}/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/orders/{order}/messages', [MessageController::class, 'store'])->middleware('throttle:20,1')->name('messages.store');
    Route::patch('/orders/{order}/accept', [OrderController::class, 'accept'])->name('orders.accept');
    Route::patch('/orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
    Route::patch('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/orders/{order}/review', [ReviewController::class, 'store'])->middleware('throttle:10,1')->name('reviews.store');
    Route::get('/reviews', [ReviewController::class, 'seller'])->name('reviews.seller');

    Route::get('/users/{user}/complain', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/users/{user}/complain', [ComplaintController::class, 'store'])->middleware('throttle:10,1')->name('complaints.store');
    Route::get('/my-complaints', [ComplaintController::class, 'index'])->name('complaints.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/compare/{laptop}', [CompareController::class, 'add'])->name('compare.add');
Route::delete('/compare/{laptop}', [CompareController::class, 'remove'])->name('compare.remove');
Route::delete('/compare', [CompareController::class, 'clear'])->name('compare.clear');
Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');

Route::prefix('admin')->middleware(['auth', 'active', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
    Route::patch('/users/{user}/reject', [AdminUserController::class, 'reject'])->name('users.reject');
    Route::patch('/users/{user}/block', [AdminUserController::class, 'block'])->name('users.block');
    Route::patch('/users/{user}/unblock', [AdminUserController::class, 'unblock'])->name('users.unblock');
    Route::patch('/users/{user}/verify', [AdminUserController::class, 'verify'])->name('users.verify');

    Route::get('/laptops', [AdminLaptopController::class, 'index'])->name('laptops.index');
    Route::get('/laptops/{laptop}', [AdminLaptopController::class, 'show'])->name('laptops.show');
    Route::patch('/laptops/{laptop}/approve', [AdminLaptopController::class, 'approve'])->name('laptops.approve');
    Route::patch('/laptops/{laptop}/reject', [AdminLaptopController::class, 'reject'])->name('laptops.reject');
    Route::patch('/laptops/{laptop}/block', [AdminLaptopController::class, 'block'])->name('laptops.block');
    Route::patch('/laptops/{laptop}/unblock', [AdminLaptopController::class, 'unblock'])->name('laptops.unblock');
    Route::patch('/laptops/{laptop}/feature', [AdminLaptopController::class, 'feature'])->name('laptops.feature');
    Route::delete('/laptops/{laptop}', [AdminLaptopController::class, 'destroy'])->name('laptops.destroy');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/accept', [OrderController::class, 'accept'])->name('orders.accept');
    Route::patch('/orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
    Route::patch('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/complaints', [AdminComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [AdminComplaintController::class, 'show'])->name('complaints.show');
    Route::patch('/complaints/{complaint}/warn', [AdminComplaintController::class, 'warn'])->name('complaints.warn');
    Route::patch('/complaints/{complaint}/block', [AdminComplaintController::class, 'block'])->name('complaints.block');
    Route::patch('/complaints/{complaint}/dismiss', [AdminComplaintController::class, 'dismiss'])->name('complaints.dismiss');

    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
});

Route::get('/sellers/{user}', [SellerProfileController::class, 'show'])->name('sellers.show');

// show route sabse neeche (create se conflict na ho)
Route::get('/laptops/{laptop}', [LaptopController::class, 'show'])->name('laptops.show');

// Phase 3: admin routes yahan aayenge
// Route::prefix('admin')->middleware(['auth','active','admin'])->group(...);

require __DIR__ . '/auth.php';
