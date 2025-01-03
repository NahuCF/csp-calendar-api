<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $with = ['subdivisions'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subdivisions()
    {
        return $this->hasMany(CountrySubdivision::class);
    }
}
