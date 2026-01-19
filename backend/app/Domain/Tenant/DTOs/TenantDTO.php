<?php

namespace App\Domain\Tenant\DTOs;

use App\Models\Tenant;

class TenantDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly array $domains,
        public readonly string $createdAt,
    ) {}

    public static function fromModel(Tenant $tenant): self
    {
        return new self(
            id: $tenant->id,
            name: $tenant->name,
            email: $tenant->email,
            domains: $tenant->domains->pluck('domain')->toArray(),
            createdAt: $tenant->created_at->toISOString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'domains' => $this->domains,
            'created_at' => $this->createdAt,
        ];
    }
}
