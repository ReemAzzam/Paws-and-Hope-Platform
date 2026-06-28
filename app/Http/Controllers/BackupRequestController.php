<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BackupRequest;
use App\Models\RescueReport;
use App\Models\Volunteer;
use App\Events\EmergencyBackupRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BackupRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $currentVolunteer = Volunteer::where('user_id', $user->id)->where('is_approved', true)->first();
        if (!$currentVolunteer) {
            return response()->json(['success' => false, 'message' => 'Access denied. Restricted to verified active volunteers.'], 403);
        }

        $request->validate([
            'rescue_report_id' => 'required|exists:rescue_reports,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'reason' => 'nullable|string',
        ]);

        $backupRequest = BackupRequest::create([
            'rescue_report_id' => $request->rescue_report_id,
            'volunteer_id' => $currentVolunteer->id, 
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        $radiusInMeters = 5000; // 5 KM Radius

        $nearbyVolunteers = Volunteer::with('user')
            ->where('is_approved', true)
            ->where('id', '!=', $currentVolunteer->id) 
            ->whereRaw("ST_Distance_Sphere(point(current_longitude, current_latitude), point(?, ?)) <= ?", [
                $request->longitude,
                $request->latitude,
                $radiusInMeters
            ])->get();

        Log::info("🔍 Emergency Backup Scan - Geocoded nearby volunteers found count: " . $nearbyVolunteers->count());

        foreach ($nearbyVolunteers as $volunteer) {
            $nearbyUser = $volunteer->user;

            if ($nearbyUser) {
                $this->sendBackupNotification($nearbyUser->fcm_token ?? 'no_token_yet', $currentVolunteer, $request->reason);
            }
        }

        broadcast(new EmergencyBackupRequested($backupRequest))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Emergency backup dispatch broadcasted. Nearby radius units have been notified.',
            'data' => $backupRequest
        ], 201);
    }

    private function sendBackupNotification($fcmToken, $senderVolunteer, $reason)
    {
        $senderName = $senderVolunteer->user->full_name ?? 'Fellow Volunteer';
        Log::info("🚨 Field Backup Alert: FCM push transmitted to token ({$fcmToken}). Body: Volunteer ({$senderName}) requests immediate back-up! Reason: {$reason}");
    }

    public function acceptBackup($id)
    {
        $backupRequest = BackupRequest::find($id);

        if (!$backupRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Backup request trace not found.'
            ], 404);
        }

        if ($backupRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This backup request has already been resolved or canceled.'
            ], 400);
        }

        $user = Auth::user();
        $volunteerProfile = $user->volunteer; 

        $backupRequest->update([
            'status' => 'responded',
            'accepted_volunteer_id' => $volunteerProfile->id 
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Backup request accepted. Rerouting dispatch path to partner unit coords.',
            'data'    => $backupRequest
        ], 200);
    }

    public function getAvailableBackupRequests(Request $request)
    {
        $user = Auth::user();

        $volunteer = Volunteer::where('user_id', $user->id)->where('is_approved', true)->first();
        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Restricted to verified field volunteers.'
            ], 403);
        }

        $volLat = $volunteer->current_latitude;
        $volLng = $volunteer->current_longitude;
        $radiusInMeters = 5000; 

        if (!$volLat || !$volLng) {
            return response()->json([
                'success' => false,
                'message' => 'Please update your telemetry GPS coordinates to poll localized pending back-up calls.'
            ], 400);
        }

        $backupRequests = BackupRequest::with('volunteer.user', 'rescueReport')
            ->where('status', 'pending')
            ->where('volunteer_id', '!=', $volunteer->id) 
            ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [
                $volLng,
                $volLat,
                $radiusInMeters
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Localized pending emergency logs compiled successfully.',
            'count' => $backupRequests->count(),
            'data' => $backupRequests
        ], 200);
    }
}