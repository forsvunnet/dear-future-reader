<?php

declare(strict_types=1);

use Domain\Dfr\Livewire\ThingPage;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::view('/', 'dfr::start')->name('dfr.things');
    Route::livewire('/things/{id}', ThingPage::class)->name('dfr.thing');
});
