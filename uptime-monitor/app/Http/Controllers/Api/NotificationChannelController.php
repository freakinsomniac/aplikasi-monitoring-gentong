<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationChannel;
use App\Models\Monitor;
use App\Jobs\SendNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $channels = NotificationChannel::where('created_by', auth('api')->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $channels
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:telegram,discord,slack,webhook',
            'config' => 'required|array',
            'config.webhook_url' => 'required_if:type,discord,slack,webhook|url',
            'config.bot_token' => 'required_if:type,telegram|string',
            'config.chat_id' => 'required_if:type,telegram|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $channel = NotificationChannel::create([
            'name' => $request->name,
            'type' => $request->type,
            'config' => $request->config,
            'created_by' => auth('api')->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification channel created successfully',
            'data' => $channel
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(NotificationChannel $notificationChannel): JsonResponse
    {
        if ($notificationChannel->created_by !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $notificationChannel
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NotificationChannel $notificationChannel): JsonResponse
    {
        if ($notificationChannel->created_by !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:telegram,discord,slack,webhook',
            'config' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $notificationChannel->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Notification channel updated successfully',
            'data' => $notificationChannel
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NotificationChannel $notificationChannel): JsonResponse
    {
        if ($notificationChannel->created_by !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $notificationChannel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification channel deleted successfully'
        ]);
    }

    /**
     * Test notification channel (FR-15)
     */
    public function test(Request $request, NotificationChannel $notificationChannel): JsonResponse
    {
        if ($notificationChannel->created_by !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Create a dummy monitor for testing
        $testMonitor = new Monitor([
            'name' => 'Test Monitor',
            'type' => 'http',
            'target' => 'https://example.com',
        ]);
        $testMonitor->id = 0;

        try {
            SendNotification::dispatch($testMonitor, 'test', null, $notificationChannel);

            return response()->json([
                'success' => true,
                'message' => 'Test notification has been queued successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage()
            ], 500);
        }
    }
}
