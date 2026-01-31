<?php

namespace App\Services;

use App\Repositories\CollaborationRepository;

class CollaborationService
{
    protected $repository;

    public function __construct(CollaborationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function all()
    {
        return $this->repository->all();
    }
}
