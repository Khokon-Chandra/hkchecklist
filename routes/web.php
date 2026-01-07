<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManageSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyRoomAttachController;
use App\Http\Controllers\PropertyRoomController;
use App\Http\Controllers\RoomTaskOrderController;
use App\Http\Controllers\PropertyRoomOrderController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomSuggestionController;
use App\Http\Controllers\RoomTaskAttachController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskMediaController;
use App\Http\Controllers\TaskSuggestionController;

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



    Route::resource('rooms', RoomController::class)->except(['show', 'create']);

    Route::post('/rooms/{room}/tasks/attach', [RoomTaskAttachController::class, 'store'])
        ->name('rooms.tasks.attach');

    Route::post(
        '/rooms/bulk-attach-tasks',
        [RoomController::class, 'bulkAttachTasks']
    )->name('rooms.bulk-attach-tasks');

    // Room-specific task management (standalone rooms, not under properties)
    Route::get('/rooms/{room}/tasks', [RoomController::class, 'tasks'])->name('rooms.tasks.index');
    Route::patch('/rooms/{room}/tasks', [RoomTaskOrderController::class, 'updateForRoom'])->name('rooms.tasks.order');
    Route::post('/rooms/{room}/tasks', [RoomController::class, 'storeTask'])->name('rooms.tasks.store');
    Route::get('/rooms/{room}/tasks/{task}/edit', [RoomController::class, 'editTask'])->name('rooms.tasks.edit');
    Route::put('/rooms/{room}/tasks/{task}', [RoomController::class, 'updateTask'])->name('rooms.tasks.update');
    Route::delete('/rooms/{room}/tasks/{task}', [RoomController::class, 'detachTask'])->name('rooms.tasks.detach');


    Route::resource('tasks', TaskController::class)->except('show');

    Route::get('/activity', [ActivityController::class, 'index'])
        ->name('activity.index');


    Route::resource('properties', PropertyController::class)->except('show');

    // Rooms and tasks (nested under property)
    Route::prefix('properties')->name('properties.')->group(function () {
        Route::get('{property}/rooms', [PropertyController::class, 'rooms'])->name('rooms.index');
        Route::get('{property}/rooms/create', [PropertyController::class, 'createRoom'])->middleware('role:admin|owner')->name('rooms.create');
        Route::get('{property}/rooms/{room}/edit', [PropertyController::class, 'editRoom'])->name('rooms.edit');
        Route::put('{property}/rooms/{room}', [PropertyController::class, 'updateRoom'])->name('rooms.update');
        Route::delete('{property}/rooms/{room}', [PropertyController::class, 'destroyRoom'])->name('rooms.destroy');
        Route::patch('{property}/rooms/order', [PropertyRoomOrderController::class, 'update'])
            ->name('rooms.order');

        Route::post('{property}/rooms/attach', [PropertyRoomAttachController::class, 'store'])
            ->name('rooms.attach');

        Route::get('{property}/rooms/{room}/tasks', [PropertyController::class, 'tasks'])->name('tasks.index');
        Route::patch('{property}/rooms/{room}/tasks', [RoomTaskOrderController::class, 'update'])->name('tasks.order');
        Route::get('{property}/rooms/{room}/tasks/create', [PropertyController::class, 'createTask'])->name('tasks.create');
        Route::post('{property}/rooms/{room}/tasks', [PropertyController::class, 'storeTask'])->name('tasks.store');
        Route::get('{property}/rooms/{room}/tasks/{task}/edit', [PropertyController::class, 'editTask'])->name('tasks.edit');
        Route::put('{property}/rooms/{room}/tasks/{task}', [PropertyController::class, 'updateTask'])->name('tasks.update');
        Route::delete('{property}/rooms/{room}/tasks/{task}', [PropertyController::class, 'detachTask'])->name('tasks.detach');


        Route::post('/tasks/{task}/media',               [TaskMediaController::class, 'store'])->name('tasks.media.store');
        Route::delete('/tasks/{task}/media/{media}',     [TaskMediaController::class, 'destroy'])->name('tasks.media.destroy');
    });

    // Users (read/assign role)
    Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::post('users/{user}/assign-role', [\App\Http\Controllers\UserController::class, 'assignRole'])->name('users.assignRole');

    // Sessions (housekeeper)
    Route::get('/sessions', [\App\Http\Controllers\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [\App\Http\Controllers\SessionController::class, 'show'])->name('sessions.show');
    Route::post('/sessions/{session}/start', [\App\Http\Controllers\SessionController::class, 'start'])->name('sessions.start');
    Route::post('/sessions/{session}/complete', [\App\Http\Controllers\SessionController::class, 'complete'])->name('sessions.complete');

    Route::post('/sessions/{session}/rooms/{room}/tasks/{task}/toggle', [ChecklistController::class, 'toggle'])
        ->name('checklist.toggle');

    Route::post('/sessions/{session}/rooms/{room}/tasks/{task}/note', [ChecklistController::class, 'note'])
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


Route::middleware(['auth'])->group(function () {
    // Autocomplete suggestions for room names
    Route::get('/rooms/suggest', [RoomSuggestionController::class, 'index'])
        ->name('rooms.suggest');

    Route::get('/tasks/suggest', [TaskSuggestionController::class, 'index'])
        ->name('tasks.suggest');

    // Property → Rooms create/store
    Route::get('/properties/{property}/rooms/create', [PropertyRoomController::class, 'create'])
        ->name('properties.rooms.create');

    Route::post('/properties/{property}/rooms', [PropertyRoomController::class, 'store'])
        ->name('properties.rooms.store');
});







require __DIR__ . '/auth.php';
