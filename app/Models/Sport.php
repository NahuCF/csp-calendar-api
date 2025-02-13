<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function events()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
