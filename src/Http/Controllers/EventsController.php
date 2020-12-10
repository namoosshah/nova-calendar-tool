<?php

namespace Czemu\NovaCalendarTool\Http\Controllers;

use Czemu\NovaCalendarTool\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventsController
{
    public function index(Request $request)
    {
        $events = Event::filter($request->query())
            ->get(['id', 'title', 'start', 'end', 'doctor_id', 'patient_id', 'live_session_id']);
        // format events
        $formattedEvents = [];
        foreach ($events as $event) {
            $formattedEvents[] = [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start,
                'end' => $event->end,
                'extendedProps' => [
                    'doctor_id' => $event->doctor_id,
                    'patient_id' => $event->patient_id,
                    'live_session_id' => $event->live_session_id,
                ]
            ];
        }
        $formattedEvents = $formattedEvents->toJson();
        return response($formattedEvents);
    }

    public function store(Request $request)
    {
        $validation = Event::getModel()->validate($request->input(), 'create');

        if ($validation->passes())
        {
            $event = Event::create($request->input());

            if ($event)
            {
                return response()->json([
                    'success' => true,
                    'event' => $event
                ]);
            }
        }

        return response()->json([
            'error' => true,
            'message' => $validation->errors()->first()
        ]);
    }

    public function update(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $validation = Event::getModel()->validate($request->input(), 'update');

        if ($validation->passes())
        {
            $event->update($request->input());

            return response()->json([
                'success' => true,
                'event' => $event
            ]);
        }

        return response()->json([
            'error' => true,
            'message' => $validation->errors()->first()
        ]);
    }

    public function destroy(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);

        if ( ! is_null($event))
        {
            $event->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => true]);
    }

    public function getDoctors() {
        $doctors = DB::table('doctors')->get();
        return response()->json([
            'status_code' => 200,
            'data' => $doctors
        ], 200);
    }

    public function getPatients() {
        $patients = DB::table('patients')->get();
        return response()->json([
            'status_code' => 200,
            'data' => $patients
        ], 200);
    }

    public function getPatientSessions($patient_id) {
        $live_sessions = DB::table("live_sessions")
                ->where("patients_id", $patient_id)
                ->get();
        return response()->json([
            'status_code' => 200,
            'data' => $live_sessions
        ], 200);
    }
}