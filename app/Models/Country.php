<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subdivisions()
    {
        return $this->hasMany(CountrySubdivision::class);
    }
}
