<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManageSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;

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
})->name('welcome');

Route::middleware('auth')->get('/dashboard', DashboardController::class)->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('properties', PropertyController::class)->except('show');


    // Rooms (nested under property)
    Route::get('properties/{property}/rooms', [\App\Http\Controllers\RoomController::class, 'index'])->name('rooms.index');
    Route::get('properties/{property}/rooms/create', [\App\Http\Controllers\RoomController::class, 'create'])->middleware('role:admin|owner')->name('rooms.create');
    Route::post('properties/{property}/rooms', [\App\Http\Controllers\RoomController::class, 'store'])->name('rooms.store');
    Route::get('properties/{property}/rooms/{room}/edit', [\App\Http\Controllers\RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('properties/{property}/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'update'])->name('rooms.update');
    Route::delete('properties/{property}/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'destroy'])->name('rooms.destroy');

    // Tasks (nested under property + room)
    Route::get('properties/{property}/rooms/{room}/tasks', [\App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
    Route::get('properties/{property}/rooms/{room}/tasks/create', [\App\Http\Controllers\TaskController::class, 'create'])->name('tasks.create');
    Route::post('properties/{property}/rooms/{room}/tasks', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::get('properties/{property}/rooms/{room}/tasks/{task}/edit', [\App\Http\Controllers\TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('properties/{property}/rooms/{room}/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');

    // Users (read/assign role)
    Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::post('users/{user}/assign-role', [\App\Http\Controllers\UserController::class, 'assignRole'])->name('users.assignRole');

    // Sessions (housekeeper)
    Route::get('/sessions', [\App\Http\Controllers\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [\App\Http\Controllers\SessionController::class, 'show'])->name('sessions.show');
    Route::post('/sessions/{session}/start', [\App\Http\Controllers\SessionController::class, 'start'])->name('sessions.start');
    Route::post('/sessions/{session}/complete', [\App\Http\Controllers\SessionController::class, 'complete'])->name('sessions.complete');

    // Checklist toggles & notes & photos
    Route::post('/sessions/{session}/checklist/{item}/toggle', [ChecklistController::class, 'toggle'])
        ->name('checklist.toggle');
    Route::post('/sessions/{session}/checklist/{item}/note', [ChecklistController::class, 'note'])
        ->name('checklist.note');
    Route::post('/sessions/{session}/rooms/{room}/photos', [\App\Http\Controllers\PhotoController::class, 'store'])->name('photos.store');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
});

Route::middleware(['auth', 'role:owner|admin'])
    ->prefix('manage')->name('manage.')
    ->group(function () {
        Route::get('sessions',        [ManageSessionController::class, 'index'])->name('sessions.index');
        Route::get('sessions/create', [ManageSessionController::class, 'create'])->name('sessions.create');
        Route::post('sessions',       [ManageSessionController::class, 'store'])->name('sessions.store');
        Route::get('sessions/{session}/edit', [ManageSessionController::class, 'edit'])->name('sessions.edit');
        Route::put('sessions/{session}',      [ManageSessionController::class, 'update'])->name('sessions.update');
        Route::delete('sessions/{session}',   [ManageSessionController::class, 'destroy'])->name('sessions.destroy');
    });


// useless routes
// Just to demo sidebar dropdown links active states.
Route::get('/buttons/text', function () {
    return view('buttons-showcase.text');
})->middleware(['auth'])->name('buttons.text');

Route::get('/buttons/icon', function () {
    return view('buttons-showcase.icon');
})->middleware(['auth'])->name('buttons.icon');

Route::get('/buttons/text-icon', function () {
    return view('buttons-showcase.text-icon');
})->middleware(['auth'])->name('buttons.text-icon');

require __DIR__ . '/auth.php';
