<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SongStageController;

Route::get('/albums/{album}/songs/{song}/{stage}', [SongStageController::class, 'show'])
    ->whereIn('stage', ['plan', 'lyrics', 'melody', 'music', 'vocals', 'mix'])
    ->name('songs.stage');

Route::post('/songs/{song}/approve', [SongStageController::class, 'approve'])
    ->name('songs.approve');

Route::post('/songs/{song}/revise', [SongStageController::class, 'revise'])
    ->name('songs.revise');
