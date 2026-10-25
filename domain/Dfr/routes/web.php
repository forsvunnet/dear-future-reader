<?php

declare(strict_types=1);

use Domain\Dfr\Livewire\ThingList;
use Domain\Dfr\Livewire\ThingPage;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::livewire('/', ThingList::class)->name('dfr.things');
    Route::livewire('/things/{id}', ThingPage::class)->name('dfr.thing');
});
