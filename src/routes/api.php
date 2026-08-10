<?php
use App\Http\Controllers\Api\MpesaC2bController;
use Illuminate\Support\Facades\Route;

Route::post('/mpesa/c2b/confirmation', [MpesaC2bController::class, 'handleConfirmation']);