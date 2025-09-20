<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachement extends Model
{
    protected $guarded = [];

    public const TABLE_NAME = 'attachements';

    public const COL_ID = 'id';

    public const COL_NAME = 'name';

    public const COL_PATH = 'path';

    public const COL_TYPE = 'type';

    public const COL_MODEL_TYPE = 'model_type';

    public const COL_MODEL_ID = 'model_id';

    public const COL_USER_ID = 'user_id';

    public const COL_CREATED_AT = 'created_at';

    public const COL_UPDATED_AT = 'updated_at';

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
