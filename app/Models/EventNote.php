<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'event_notes';

    protected $with = ['user'];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
