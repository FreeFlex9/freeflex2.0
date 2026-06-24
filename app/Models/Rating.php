<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = ['demand_id', 'company_id', 'provider_id', 'score', 'comment', 'rated_by'];

    public function demand()   { return $this->belongsTo(Demand::class); }
    public function company()  { return $this->belongsTo(Company::class); }
    public function provider() { return $this->belongsTo(Provider::class); }
}
