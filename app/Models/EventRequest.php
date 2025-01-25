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
        return $this->hasMany(EventRequestDetail::class)->orderBy('id', 'desc');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }
}
