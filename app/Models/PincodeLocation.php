<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PincodeLocation extends Model
{
    protected $fillable = [
        'pincode',
        'latitude',
        'longitude',
        'city',
        'state',
        'country',
    ];
}
