<?php

use Illuminate\Support\Facades\Route;


Route::resource('vue-routers', VueRouterController::class)->except(['show']);

Route::resource('admin-permissions', AdminPermissionController::class);

Route::resource('admin-roles', AdminRoleController::class);
