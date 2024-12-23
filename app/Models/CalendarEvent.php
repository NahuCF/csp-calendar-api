<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['notes'];

    public function resource()
    {
        return $this->belongsTo(CalendarResource::class, 'calendar_resource_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function historyEvents()
    {
        return $this->hasMany(HistoryEvent::class);
    }

    public function notes()
    {
        return $this->hasMany(EventNote::class)->latest();
    }
}
