<?php

namespace App\Repositories;

use App\Models\TeamPartnership;

class TeamPartnershipRepository
{
    public function create(array $data): TeamPartnership
    {
        return TeamPartnership::create($data);
    }

    public function find($id): ?TeamPartnership
    {
        return TeamPartnership::find($id);
    }

    public function all()
    {
        return TeamPartnership::all();
    }
}
