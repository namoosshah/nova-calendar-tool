<?php

namespace Czemu\NovaCalendarTool\Http\Controllers;

use Czemu\NovaCalendarTool\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventsController
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $events = Event::with(['patient', 'doctor'])->where('doctor_id', $user->id)
            ->orWhere('patient_id', $user->id)->filter($request->query())
            ->get(['id', 'title', 'start', 'end', 'doctor_id', 'patient_id', 'live_session_id', 'completed_at']);
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
                    'completed_at' => $event->completed_at,
                    'htmlContent' => $this->createEventContent($event)
                ],
                'color' => $event->completed_at ? '#34D399' : '#FCD34D'
            ];
        }
        $formattedEvents = collect($formattedEvents)->toJson();
        return response($formattedEvents);
    }

    public function createEventContent($event) {
        $start = \Carbon\Carbon::parse($event->start)->format('H:m');
        $end = \Carbon\Carbon::parse($event->end)->format('H:m');
        $doctor = $event->doctor->first_name.' '.$event->doctor->last_name;
        $patient = $event->patient->first_name.' '.$event->patient->last_name;
        $range = $start . ' - ' . $end;
        $html = '<div class="text-sm"><div class="flex justify-between">'.
            '<div>'.$event->title.'</div>'.
            '<a class="editEvent"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current"><path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path></svg></a>'.
            '</div>'.
            '<div>Doctor: '.$doctor .'</div>'.
            '<div>Patient: '.$patient .'</div>'.
            '<div>Live Session ID: '.$event->live_session_id .'</div>'.
            '<div>Time: '. $range .'</div></div>';
        return $html;
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

    public function markCompleted(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $event->update([
            'completed_at' => \Carbon\Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'event' => $event
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
        $user = auth()->user();
        $query = DB::table('doctors');
        if ($this->getType($user) == "doctor") {
            $query->where("id", $user->id);
        }
        $doctors = $query->get();
        return response()->json([
            'status_code' => 200,
            'data' => $doctors
        ], 200);
    }

    public function getPatients() {
        $user = auth()->user();
        $query = DB::table('patients');
        if ($this->getType($user) == "patient") {
            $query->where("id", $user->id);
        }
        $patients = $query->get();
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

    private function getType($user) {
        return $user->patient ? "patient" : ($user->doctor ? "doctor" : "");
    }
}