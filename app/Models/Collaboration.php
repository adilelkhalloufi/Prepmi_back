<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collaboration extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'social_url_1',
        'social_url_2',
        'social_url_3',
        'email',
        'phone',
        'country',
    ];
}
