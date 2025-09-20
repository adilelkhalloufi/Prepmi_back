<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profil extends Model
{
    use HasFactory;

    public const TABLE_NAME = 'profils';

    public const COL_ID = 'id';

    public const COL_COMPANY_NAME = 'company_name';

    public const COL_COMPANY_ADDRESS = 'company_address';

    public const COL_COMPANY_LOGO = 'company_logo';

    public const COL_LATITUDE = 'latitude';

    public const COL_LONGITUDE = 'longitude';

    public const COL_COINS = 'coins';

    public const COL_USER_ID = 'user_id';

    public const COL_URL = 'url';

    public const COL_WEBSITE = 'website';

    public const COL_RATE = 'rate';

    public const COL_CREATED_AT = 'created_at';

    public const COL_UPDATED_AT = 'updated_at';

    //

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
