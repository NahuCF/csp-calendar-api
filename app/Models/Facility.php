<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['resources'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resources()
    {
        return $this->hasMany(CalendarResource::class)->without('facility');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
