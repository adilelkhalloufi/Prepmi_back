<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public const TABLE_NAME = 'users';

    public const COL_ID = 'id';

    public const COL_FIRST_NAME = 'first_name';

    public const COL_LAST_NAME = 'last_name';

    public const COL_EMAIL = 'email';

    public const COL_PHONE = 'phone';

    public const COL_ADDRESS = 'address';

    public const COL_CITY_ID = 'city_id';

    public const COL_ROLE = 'role';

    public const COL_STATUS = 'status';

    public const COL_SPECIALITIE_ID = 'specialitie_id';

    public const COL_EMAIL_VERIFIED_AT = 'email_verified_at';

    public const COL_CODE_VERIFY = 'code_verify';

    public const COL_PASSWORD = 'password';

    public const COL_REMEMBER_TOKEN = 'remember_token';

    public const COL_CREATED_AT = 'created_at';

    public const COL_UPDATED_AT = 'updated_at';

    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }


    public function profil(): HasOne
    {
        return $this->hasOne(Profil::class, 'user_id', 'id');
    }


    // function to send code verfecation

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
