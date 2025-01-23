<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRequestDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['resource'];

    public function resource()
    {
        return $this->belongsTo(CalendarResource::class, 'calendar_resource_id');
    }
}
