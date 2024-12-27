<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $with = ['notes', 'client'];

    public function resource()
    {
        return $this->belongsTo(CalendarResource::class, 'calendar_resource_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
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
