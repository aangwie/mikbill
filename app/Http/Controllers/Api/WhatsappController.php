<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Validator;

class WhatsappController extends Controller
{
    /**
     * Send WhatsApp Message via API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request)
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'number' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Auth Check
        $user = User::where('api_token', $request->api_key)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid API Key'
            ], 401);
        }

        // 3. Send Logic
        $target = $request->number;
        $message = $request->message;

        try {
            $response = WhatsappService::send($target, $message);

            if ($response['status'] === true) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Message processed',
                    'data' => $response
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed: ' . ($response['message'] ?? 'Unknown error')
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
