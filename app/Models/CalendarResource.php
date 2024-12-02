<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarResource extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function calendarResourceType()
    {
        return $this->belongsTo(CalendarResourceType::class);
    }

    public function events()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
