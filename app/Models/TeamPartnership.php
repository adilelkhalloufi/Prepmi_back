<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPartnership extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_title',
        'email',
        'first_name',
        'last_name',
        'company_name',
        'company_website',
        'partnership_type',
        'team_members_per_week',
        'products_interested',
        'heard_about_us',
        'accept_terms',
        'accept_communications',
    ];

    protected $casts = [
        'products_interested' => 'array',
        'accept_terms' => 'boolean',
        'accept_communications' => 'boolean',
    ];
}
