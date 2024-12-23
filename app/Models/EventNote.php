<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventNote extends Model
{
    use HasFactory;

    protected $table = 'event_notes';

    protected $with = ['user'];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
