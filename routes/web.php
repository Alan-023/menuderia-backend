<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use Illuminate\Support\Facades\Artisan;

Route::get('/correr-migraciones', function () {
    Artisan::call('migrate', ['--force' => true]);
    return "Migraciones coronadas con exito en la nube!";
});