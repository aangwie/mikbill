<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsappController;

Route::post('/send-message', [WhatsappController::class, 'sendMessage']);
