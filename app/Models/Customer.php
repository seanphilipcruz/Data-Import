<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'email',
        'gender',
        'country',
        'city',
        'phone'
    ];

    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
