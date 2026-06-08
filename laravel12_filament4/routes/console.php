<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('ov500:about', function (): void {
    $this->info('OV500 Laravel 12 / Filament 4 portal scaffold');
})->purpose('Display information about the OV500 Laravel rebuild');
