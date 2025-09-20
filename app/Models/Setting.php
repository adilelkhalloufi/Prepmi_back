<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const TABLE_NAME = 'settings';

    public const COL_ID = 'id';

    public const COL_WHATSAPP = 'whatsapp';

    public const COL_INSTAGRAM = 'instagram';

    public const COL_FACEBOOK = 'facebook';

    public const COL_TWITTER = 'twitter';

    public const COL_YOUTUBE = 'youtube';

    public const COL_LINKEDIN = 'linkedin';

    public const COL_DISCORD = 'discord';

    public const COL_TELEGRAM = 'telegram';

    public const COL_TIKTOK = 'tiktok';

    public const COL_SHOW_PRICES_GUESTS = 'show_prices_guests';

    public const COL_SHOW_PRICES_USERS = 'show_prices_users';

    public const COL_CREATED_AT = 'created_at';

    public const COL_UPDATED_AT = 'updated_at';
}
