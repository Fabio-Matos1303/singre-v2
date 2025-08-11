<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Swagger UI (dev only)
if (app()->environment(['local', 'testing'])) {
    Route::get('/api/docs', function () {
        return view('l5-swagger::index');
    });
}

