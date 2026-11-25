<?php

use App\Http\Controllers\DogFacts\DestroyController;
use App\Http\Controllers\DogFacts\IndexController;
use App\Http\Controllers\DogFacts\MetadataController;
use App\Http\Controllers\DogFacts\ShowController;
use App\Http\Controllers\DogFacts\StoreController;
use App\Http\Controllers\DogFacts\UpdateController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'dog-facts'], function () {
    Route::get('metadata', MetadataController::class)->name('api.v2.dog-facts.metadata');
    Route::get('{id}', ShowController::class)->whereNumber('id')->name('api.v2.dog-facts.show');
    Route::put('{dogFact}', UpdateController::class)->whereNumber('dogFact')->name('api.v2.dog-facts.update');
    Route::delete('{dogFact}', DestroyController::class)->whereNumber('dogFact')->name('api.v2.dog-facts.destroy');
    Route::post('/', StoreController::class)->name('api.v2.dog-facts.store');
    Route::get('/', IndexController::class)->name('api.v2.dog-facts.index');
});
