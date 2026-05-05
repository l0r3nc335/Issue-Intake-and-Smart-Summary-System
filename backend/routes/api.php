<?php

use App\Http\Controllers\IssueController;
use Illuminate\Support\Facades\Route;

Route::prefix('issues')->group(function (): void {
    Route::get('/', [IssueController::class, 'index']);
    Route::post('/', [IssueController::class, 'store']);
    Route::get('/{issue}', [IssueController::class, 'show']);
    Route::patch('/{issue}', [IssueController::class, 'update']);
});
