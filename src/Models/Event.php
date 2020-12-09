<?php

namespace Czemu\NovaCalendarTool\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Event extends Model
{
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime'
    ];

    protected $guarded = ['id'];

    public function validate($data, $scenario)
    {
        switch ($scenario)
        {
            case 'create':
            case 'update':
                $rules = [
                    'title' => 'required',
                    'start' => 'required|date',
                    'end' => 'required|date|after_or_equal:start'
                ];

                break;
        }

        return Validator::make($data, $rules);
    }

    public function scopeFilter($query, $data)
    {
        if ( ! empty($data['start']))
        {
            $query->where('start', '>=', $data['start']);
        }

        if ( ! empty($data['end']))
        {
            $query->where('end', '<=', $data['end']);
        }

        if ( ! empty($data['doctor_id']))
        {
            $query->where('doctor_id', '=', $data['doctor_id']);
        }

        if ( ! empty($data['patient_id']))
        {
            $query->where('patient_id', '=', $data['patient_id']);
        }

        if ( ! empty($data['live_session_id']))
        {
            $query->where('live_session_id', '=', $data['live_session_id']);
        }

        return $query;
    }
}