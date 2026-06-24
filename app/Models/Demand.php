<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demand extends Model
{
    protected $fillable = [
        'company_id', 'service_id', 'title', 'description',
        'date', 'start_time', 'end_time', 'slots_needed', 'slots_confirmed',
        'zip_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
        'status',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
