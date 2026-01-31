<?php

namespace App\Repositories;

use App\Models\Collaboration;

class CollaborationRepository
{
    public function create(array $data): Collaboration
    {
        return Collaboration::create($data);
    }

    public function find($id): ?Collaboration
    {
        return Collaboration::find($id);
    }

    public function all()
    {
        return Collaboration::all();
    }
}
