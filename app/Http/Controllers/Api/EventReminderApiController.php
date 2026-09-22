<?php

namespace App\Http\Controllers\Api;

use \App\Models\Event;
use \App\Models\EventReminder;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class EventReminderApiController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/events/{uuid}/remind",
     *     summary="Set a reminder for an event",
     *     tags={"Event Reminders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the event",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type of reminder (1_DAY_BEFORE, 1_HOUR_BEFORE, AT_START)",
     *         required=false,
     *         @OA\Schema(type="string", default="1_DAY_BEFORE")
     *     ),
     *     @OA\Response(response=200, description="Reminder set successfully"),
     *     @OA\Response(response=404, description="Event not found")
     * )
     */
    public function setReminder(Request $request, $uuid)
    {
        $userId = auth()->id();
        $event = Event::where('uuid', $uuid)->first();

        if (!$event) {
            return $this->responseJson(false, 404, 'Event not found');
        }

        $type = $request->input('type', '1_DAY_BEFORE');
        $validTypes = ['1_DAY_BEFORE', '1_HOUR_BEFORE', 'AT_START'];
        if (!in_array($type, $validTypes)) {
            return $this->responseJson(false, 400, 'Invalid reminder type');
        }

        $eventDate = \Carbon\Carbon::parse($event->event_date . ' ' . $event->start_time);

        $remindAt = null;
        if ($type === '1_DAY_BEFORE') {
            $remindAt = $eventDate->copy()->subDay();
        } elseif ($type === '1_HOUR_BEFORE') {
            $remindAt = $eventDate->copy()->subHour();
        } elseif ($type === 'AT_START') {
            $remindAt = $eventDate->copy();
        }

        EventReminder::updateOrCreate(
            ['user_id' => $userId, 'event_id' => $event->id],
            ['type' => $type, 'remind_at' => $remindAt, 'is_sent' => 0]
        );

        return $this->responseJson(true, 200, 'Reminder set successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/events/{uuid}/remove-reminder",
     *     summary="Remove a reminder for an event",
     *     tags={"Event Reminders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the event",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Reminder removed successfully"),
     *     @OA\Response(response=404, description="Event not found")
     * )
     */
    public function removeReminder($uuid)
    {
        $userId = auth()->id();
        $event = Event::where('uuid', $uuid)->first();

        if (!$event) {
            return $this->responseJson(false, 404, 'Event not found');
        }

        $reminder = EventReminder::where('user_id', $userId)
            ->where('event_id', $event->id)
            ->first();

        if ($reminder) {
            $reminder->delete();
            return $this->responseJson(true, 200, 'Reminder removed successfully');
        }

        return $this->responseJson(false, 400, 'No reminder found to remove');
    }
}
