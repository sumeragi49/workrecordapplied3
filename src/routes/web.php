<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

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

Route::middleware(['guest'])->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');

    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'attendance']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('/attendance/start', [AttendanceController::class, 'workStart'])->name('work.start');

    Route::patch('/attendance/end', [AttendanceController::class, 'workEnd'])->name('work.end');

    Route::post('/break/start', [AttendanceController::class, 'breakStart'])->name('break.start');

    Route::patch('/break/end', [AttendanceController::class, 'breakEnd'])->name('break.end');

    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::get('/attendance/detail/{attendanceId}', [AttendanceController::class, 'show'])->name('attendance.show');

    Route::post('/attendance/detail/store/{attendanceId}', [AttendanceController::class, 'requestStore'])->name('attendance.request');

    Route::post('/attendance/staff', [AttendanceController::class, 'newAttendance'])->name('new.attendance');
});

Route::middleware(['auth','admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('admin.attendance.index');

    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');

    Route::get('/admin/attendance/{id}', [AdminController::class, 'show'])->name('admin.attendance.show');

    Route::post('/admin/attendance/store/{attendanceId}', [AdminController::class, 'requestStore'])->name('admin.attendance.request');

    Route::get('/admin/staff/list', [AdminController::class, 'staffList']);

    Route::get('/admin/attendance/staff/{attendanceId}', [AdminController::class, 'staffAttendance'])->name('admin.staff.attendance');

    Route::post('/admin/attendance/staff/{userId}', [AdminController::class, 'newAttendance'])->name('admin.new.attendance');
});

Route::middleware(['auth', 'CheckRole'])->group(function () {
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('request.list');

    Route::get('/stamp_correction_request/approval/{attendanceCorrectRequestId}', [AttendanceController::class, 'approval'])->name('request.approval');

    Route::post('/stamp_correction_request/approval/{attendanceCorrectRequestId}/store', [AttendanceController::class, 'approvalStore'])->name('approval.store');
});
