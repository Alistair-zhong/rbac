<?php

use Illuminate\Support\Facades\Route;


Route::resource('vue-routers', VueRouterController::class)->except(['show']);
Route::resource('admin-permissions', AdminPermissionController::class)->except(['show']);
Route::resource('admin-roles', AdminRoleController::class)->except(['show']);
