<?php

use Illuminate\Support\Facades\Route;

// bulk update role permission rouer
Route::post('admin-roles/bulk-update', [Rbac\Controllers\AdminRoleController::class, 'bulkUpdate'])->name('admin-roles.bulk-update');
Route::post('vue-routers/by-import', [Rbac\Controllers\VueRouterController::class, 'importVueRouters'])->name('vue-routers.by-import');
Route::put('vue-routers', [Rbac\Controllers\VueRouterController::class, 'batchUpdate'])->name('vue-routers.batch.update');

Route::resource('admin-users', AdminUserController::class)->except(['show']);

Route::resource('vue-routers', VueRouterController::class)->except(['show']);

Route::resource('admin-permissions', AdminPermissionController::class)->except(['show']);


Route::resource('admin-roles', AdminRoleController::class)->except(['show']);
