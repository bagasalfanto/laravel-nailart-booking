<?php

use App\Http\Controllers\Auth\AccountSetupController;
use App\Http\Controllers\Customer\AppointmentController as CustomerAppointmentController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Landing\HomeController;
use App\Http\Controllers\Landing\NaillistController;
use App\Http\Controllers\Landing\PricelistController;
use App\Http\Controllers\Landing\ScheduleController;
use App\Http\Controllers\Dashboard\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Dashboard\Admin\CharmManagementController as AdminCharmManagementController;
use App\Http\Controllers\Dashboard\Admin\PaymentMonitoringController as AdminPaymentMonitoringController;
use App\Http\Controllers\Dashboard\Admin\PortfolioModerationController as AdminPortfolioModerationController;
use App\Http\Controllers\Dashboard\Admin\SpecialtyController as AdminSpecialtyController;
use App\Http\Controllers\Dashboard\Admin\TreatmentSettingController as AdminTreatmentSettingController;
use App\Http\Controllers\Dashboard\Admin\WebSettingController as AdminWebSettingController;
use App\Http\Controllers\Dashboard\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Dashboard\Nailist\BookingController as NailistBookingController;
use App\Http\Controllers\Dashboard\Nailist\PortfolioController as NailistPortfolioController;
use App\Http\Controllers\Dashboard\Superadmin\ActivityLogController as SuperadminActivityLogController;
use App\Http\Controllers\Dashboard\Superadmin\RoleController as SuperadminRoleController;
use App\Http\Controllers\Dashboard\Superadmin\SystemSettingController as SuperadminSystemSettingController;
use App\Http\Controllers\Dashboard\Superadmin\UserController as SuperadminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/',          [HomeController::class, 'index'])->name('home');
Route::get('/naillist',           [NaillistController::class, 'index'])->name('naillist');
Route::get('/naillist/{nailist}', [NaillistController::class, 'show'])->name('naillist.show');
Route::get('/pricelist', [PricelistController::class, 'index'])->name('pricelist');
Route::get('/schedule',         [ScheduleController::class, 'index'])->name('schedule');
Route::get('/schedule/events',  [ScheduleController::class, 'events'])->name('schedule.events');
Route::get('/schedule/export',  [ScheduleController::class, 'export'])
    ->middleware(['auth', 'role:admin|superadmin|nailist'])
    ->name('schedule.export');

// Customer-only landing routes
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/appointments', [CustomerAppointmentController::class, 'index'])->name('customer.appointments.index');

    // Booking flow: step1 (jadwal) → step2 (treatment+charm) → step3 (data + submit ke Midtrans).
    Route::prefix('book')->name('booking.')->group(function () {
        Route::get ('/',                [CustomerBookingController::class, 'step1'])->name('step1');
        Route::get ('/step1',           fn () => redirect()->route('booking.step1'));
        Route::post('/step1',           [CustomerBookingController::class, 'submitStep1'])->name('step1.submit');
        Route::get ('/review',          [CustomerBookingController::class, 'step2'])->name('step2');
        Route::post('/review',          [CustomerBookingController::class, 'submitStep2'])->name('step2.submit');
        Route::get ('/payment',         [CustomerBookingController::class, 'step3'])->name('step3');
        Route::post('/payment',         [CustomerBookingController::class, 'submitStep3'])->name('step3.submit');
        Route::get ('/payment/waiting', [CustomerBookingController::class, 'paymentWaiting'])->name('payment.waiting');
        Route::get ('/payment/finish',  [CustomerBookingController::class, 'paymentFinish'])->name('payment.finish');
        Route::get ('/payment/success', [CustomerBookingController::class, 'paymentSuccess'])->name('payment.success');
        Route::get ('/payment/failed',  [CustomerBookingController::class, 'paymentFailed'])->name('payment.failed');
    });
});

// Midtrans webhook — publik, no auth, CSRF-exempt (lihat bootstrap/app.php).
Route::post('/payment/notification', \App\Http\Controllers\Webhook\MidtransNotificationController::class)
    ->name('payment.notification');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/account/setup',  [AccountSetupController::class, 'show'])->name('account.setup');
    Route::post('/account/setup', [AccountSetupController::class, 'update'])->name('account.setup.update');
});

/*
|--------------------------------------------------------------------------
| Dashboard — semua menu role berada di prefix /dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        // Home dashboard (dispatch by role di controller)
        Route::get('/', [DashboardController::class, 'index'])->name('home');

        /*
         * Superadmin: User & Role management
         */
        Route::middleware('role:admin|superadmin')->group(function () {
            Route::get('/users',                            [SuperadminUserController::class, 'index'])->name('users.index');
            Route::get('/users/data',                       [SuperadminUserController::class, 'data'])->name('users.data');
            Route::get('/users/export',                     [SuperadminUserController::class, 'export'])->name('users.export');
            Route::post('/users',                           [SuperadminUserController::class, 'store'])->name('users.store');
            Route::post('/users/{user}/regenerate-password',[SuperadminUserController::class, 'regeneratePassword'])->name('users.regenerate-password');
            Route::delete('/users/{user}',                  [SuperadminUserController::class, 'destroy'])->name('users.destroy');
            Route::get('/users/{user}',                     [SuperadminUserController::class, 'show'])->name('users.show');
        });

        Route::middleware('role:superadmin')->group(function () {
            Route::get('/users/{user}/edit', [SuperadminUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}',      [SuperadminUserController::class, 'update'])->name('users.update');

            Route::get('/roles',             [SuperadminRoleController::class, 'index'])->name('roles.index');
            Route::get('/roles/data',        [SuperadminRoleController::class, 'data'])->name('roles.data');
            Route::get('/roles/export',      [SuperadminRoleController::class, 'export'])->name('roles.export');
            Route::get('/roles/create',      [SuperadminRoleController::class, 'create'])->name('roles.create');
            Route::post('/roles',            [SuperadminRoleController::class, 'store'])->name('roles.store');
            Route::get('/roles/{role}/edit', [SuperadminRoleController::class, 'edit'])->name('roles.edit');
            Route::put('/roles/{role}',      [SuperadminRoleController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{role}',   [SuperadminRoleController::class, 'destroy'])->name('roles.destroy');

            Route::get('/activity-log',                 [SuperadminActivityLogController::class, 'index'])->name('activity-log.index');
            Route::get('/activity-log/data',            [SuperadminActivityLogController::class, 'data'])->name('activity-log.data');
            Route::get('/activity-log/export',          [SuperadminActivityLogController::class, 'export'])->name('activity-log.export');
            Route::get('/activity-log/{activity}',      [SuperadminActivityLogController::class, 'show'])->name('activity-log.show');

            Route::get('/system-settings',                       [SuperadminSystemSettingController::class, 'index'])->name('system-settings.index');
            Route::put('/system-settings/email-domain',          [SuperadminSystemSettingController::class, 'updateEmailDomain'])->name('system-settings.update-email-domain');
        });

        /*
         * Admin & Superadmin: Review moderation
         */
        Route::middleware('role:admin|superadmin')->group(function () {
            Route::get('/reviews',                    [AdminReviewController::class, 'index'])->name('reviews.index');
            Route::get('/reviews/data',               [AdminReviewController::class, 'data'])->name('reviews.data');
            Route::get('/reviews/export',             [AdminReviewController::class, 'export'])->name('reviews.export');
            Route::patch('/reviews/{review}/feature', [AdminReviewController::class, 'toggleFeature'])->name('reviews.feature');
            Route::delete('/reviews/{review}',        [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

            // Treatment Settings (kategori + treatment).
            Route::prefix('treatments')->name('treatments.')->group(function () {
                Route::get('/',                                  [AdminTreatmentSettingController::class, 'index'])->name('index');

                // Categories
                Route::post('/categories',                       [AdminTreatmentSettingController::class, 'storeCategory'])->name('categories.store');
                Route::post('/categories/reorder',               [AdminTreatmentSettingController::class, 'reorderCategories'])->name('categories.reorder');
                Route::put('/categories/{category}',             [AdminTreatmentSettingController::class, 'updateCategory'])->name('categories.update');
                Route::delete('/categories/{category}',          [AdminTreatmentSettingController::class, 'destroyCategory'])->name('categories.destroy');

                // Treatments
                Route::post('/items',                            [AdminTreatmentSettingController::class, 'storeTreatment'])->name('items.store');
                Route::post('/items/reorder',                    [AdminTreatmentSettingController::class, 'reorderTreatments'])->name('items.reorder');
                Route::put('/items/{treatment}',                 [AdminTreatmentSettingController::class, 'updateTreatment'])->name('items.update');
                Route::delete('/items/{treatment}',              [AdminTreatmentSettingController::class, 'destroyTreatment'])->name('items.destroy');
            });

            // Payment Monitoring (admin/superadmin)
            Route::get('/payment-monitoring',                              [AdminPaymentMonitoringController::class, 'index'])->name('payment-monitoring.index');
            Route::get('/payment-monitoring/data',                         [AdminPaymentMonitoringController::class, 'data'])->name('payment-monitoring.data');
            Route::get('/payment-monitoring/export',                       [AdminPaymentMonitoringController::class, 'export'])->name('payment-monitoring.export');
            Route::patch('/payment-monitoring/{payment}/confirm',          [AdminPaymentMonitoringController::class, 'confirmPayment'])->name('payment-monitoring.confirm');
            Route::patch('/payment-monitoring/{payment}/pelunasan',       [AdminPaymentMonitoringController::class, 'storePelunasan'])->name('payment-monitoring.pelunasan');

            // Charm Management (admin/superadmin)
            Route::get('/charm-management',                                [AdminCharmManagementController::class, 'index'])->name('charm-management.index');
            Route::post('/charm-management',                               [AdminCharmManagementController::class, 'store'])->name('charm-management.store');
            Route::put('/charm-management/{charm}',                        [AdminCharmManagementController::class, 'update'])->name('charm-management.update');
            Route::delete('/charm-management/{charm}',                     [AdminCharmManagementController::class, 'destroy'])->name('charm-management.destroy');

            // Portfolio moderation (admin/superadmin pilih portfolio yang tampil di home)
            Route::get('/portfolio-moderation',                            [AdminPortfolioModerationController::class, 'index'])->name('portfolio-moderation.index');
            Route::get('/portfolio-moderation/data',                       [AdminPortfolioModerationController::class, 'data'])->name('portfolio-moderation.data');
            Route::get('/portfolio-moderation/export',                     [AdminPortfolioModerationController::class, 'export'])->name('portfolio-moderation.export');
            Route::patch('/portfolio-moderation/{portfolio}/toggle-feature',[AdminPortfolioModerationController::class, 'toggleFeature'])->name('portfolio-moderation.toggle');

            Route::get('/specialties',              [AdminSpecialtyController::class, 'index'])->name('specialties.index');
            Route::get('/specialties/data',         [AdminSpecialtyController::class, 'data'])->name('specialties.data');
            Route::get('/specialties/export',       [AdminSpecialtyController::class, 'export'])->name('specialties.export');
            Route::post('/specialties',             [AdminSpecialtyController::class, 'store'])->name('specialties.store');
            Route::put('/specialties/{specialty}',  [AdminSpecialtyController::class, 'update'])->name('specialties.update');
            Route::delete('/specialties/{specialty}', [AdminSpecialtyController::class, 'destroy'])->name('specialties.destroy');

            Route::get('/web-settings',                  [AdminWebSettingController::class, 'index'])->name('web-settings.index');
            Route::post('/web-settings',                 [AdminWebSettingController::class, 'store'])->name('web-settings.store');
            Route::put('/web-settings',                  [AdminWebSettingController::class, 'update'])->name('web-settings.update');
            Route::delete('/web-settings/{webSetting}',  [AdminWebSettingController::class, 'destroy'])->name('web-settings.destroy');
        });

        /*
         * Nailist: Portfolio CRUD
         */
        Route::middleware('role:nailist')->group(function () {
            Route::get('/nailist-bookings',             [NailistBookingController::class, 'index'])->name('nailist-bookings.index');
            Route::get('/nailist-bookings/data',        [NailistBookingController::class, 'data'])->name('nailist-bookings.data');
            Route::get('/nailist-bookings/{booking}',   [NailistBookingController::class, 'show'])->name('nailist-bookings.show');
            Route::patch('/nailist-bookings/{booking}/start',  [NailistBookingController::class, 'startSession'])->name('nailist-bookings.start');
            Route::patch('/nailist-bookings/{booking}/finish', [NailistBookingController::class, 'finishSession'])->name('nailist-bookings.finish');
            Route::delete('/nailist-bookings/{booking}',[NailistBookingController::class, 'destroy'])->name('nailist-bookings.destroy');

            Route::get('/portfolio',                   [NailistPortfolioController::class, 'index'])->name('portfolio.index');
            Route::get('/portfolio/create',            [NailistPortfolioController::class, 'create'])->name('portfolio.create');
            Route::post('/portfolio',                  [NailistPortfolioController::class, 'store'])->name('portfolio.store');
            Route::get('/portfolio/{portfolio}/edit',  [NailistPortfolioController::class, 'edit'])->name('portfolio.edit');
            Route::put('/portfolio/{portfolio}',       [NailistPortfolioController::class, 'update'])->name('portfolio.update');
            Route::delete('/portfolio/{portfolio}',    [NailistPortfolioController::class, 'destroy'])->name('portfolio.destroy');
        });

        /*
         * Customer: Review pribadi
         */
        Route::middleware('role:customer|superadmin')->group(function () {
            Route::get('/my-reviews',                    [CustomerReviewController::class, 'index'])->name('my-reviews.index');
            Route::get('/my-reviews/{reservasi}/create', [CustomerReviewController::class, 'create'])->name('my-reviews.create');
            Route::post('/my-reviews/{reservasi}',       [CustomerReviewController::class, 'store'])->name('my-reviews.store');
            Route::get('/my-reviews/{review}/edit',      [CustomerReviewController::class, 'edit'])->name('my-reviews.edit');
            Route::put('/my-reviews/{review}',           [CustomerReviewController::class, 'update'])->name('my-reviews.update');
            Route::delete('/my-reviews/{review}',        [CustomerReviewController::class, 'destroy'])->name('my-reviews.destroy');
        });
    });

require __DIR__.'/auth.php';
