<?php

use App\Http\Controllers\LegacyPortalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/dashboard', [LegacyPortalController::class, 'dashboard'])->name('legacy.dashboard');

Route::get('/{legacyController}/{legacyAction?}/{legacyParameters?}', [LegacyPortalController::class, 'handle'])
    ->where('legacyController', 'account|bundle|carriers|currency|dashboard|dialplans|dids|download|endpoints|login|logout|module|page|providers|ratecard|rates|recyclebin|reports|roles|routes|sitesetup|sysconfig|tariffs|upload|users')
    ->where('legacyParameters', '.*')
    ->name('legacy.controller');
