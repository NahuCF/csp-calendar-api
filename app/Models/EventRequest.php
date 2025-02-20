<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(CalendarEvent::class)->orderBy('id', 'desc');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }
}
