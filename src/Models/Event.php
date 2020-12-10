<?php

namespace Czemu\NovaCalendarTool\Models;

use App\Models\Doctor;
use App\Models\Patient;
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
                    'end' => 'required|date|after_or_equal:start',
                    'doctor_id' => 'required',
                    'patient_id' => 'required',
                    'live_session_id' => 'required'
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function patient() {
        return $this->hasOne(Patient::class, 'id', 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function doctor() {
        return $this->hasOne(Doctor::class, 'id', 'doctor_id');
    }
}