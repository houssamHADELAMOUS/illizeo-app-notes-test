<?php

namespace App\Domain\Tenant\Repositories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

class TenantRepository
{
    public function __construct(
        private Tenant $model
    ) {}

    public function all(): Collection
    {
        return $this->model->with('domains')->get();
    }

    public function find(string $id): ?Tenant
    {
        return $this->model->with('domains')->find($id);
    }

    public function findOrFail(string $id): Tenant
    {
        return $this->model->with('domains')->findOrFail($id);
    }

    public function create(array $data): Tenant
    {
        return $this->model->create($data);
    }

    public function delete(string $id): bool
    {
        return $this->findOrFail($id)->delete();
    }
}
