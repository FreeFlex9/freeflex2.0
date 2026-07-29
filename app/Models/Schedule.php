<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'provider_id', 'demand_id', 'date', 'start_time', 'end_time', 'description', 'status',
        'check_in_at', 'check_in_lat', 'check_in_lng', 'check_in_distance_m',
        'check_out_at', 'check_out_lat', 'check_out_lng', 'check_out_distance_m',
        'no_show_status', 'no_show_detected_at', 'no_show_justification',
        'no_show_justification_submitted_at', 'no_show_justification_deadline',
        'no_show_reviewed_at', 'no_show_reviewed_by_admin_id', 'no_show_penalty_applied',
    ];

    protected function casts(): array
    {
        return [
            'date'                                => 'date',
            'check_in_at'                         => 'datetime',
            'check_out_at'                         => 'datetime',
            'no_show_detected_at'                  => 'datetime',
            'no_show_justification_submitted_at'   => 'datetime',
            'no_show_justification_deadline'       => 'datetime',
            'no_show_reviewed_at'                  => 'datetime',
            'no_show_penalty_applied'              => 'boolean',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function demand()
    {
        return $this->belongsTo(Demand::class);
    }

    public function reviewedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'no_show_reviewed_by_admin_id');
    }
}
