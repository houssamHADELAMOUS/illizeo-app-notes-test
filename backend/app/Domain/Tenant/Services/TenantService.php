<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\DTOs\CreateTenantDTO;
use App\Domain\Tenant\DTOs\TenantDTO;
use App\Domain\Tenant\Repositories\TenantRepository;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantService
{
    public function __construct(
        private TenantRepository $tenantRepository
    ) {}

    /**
     * Get all tenants
     */
    public function getAllTenants(): array
    {
        return $this->tenantRepository->all()
            ->map(fn(Tenant $tenant) => TenantDTO::fromModel($tenant)->toArray())
            ->toArray();
    }

    /**
     * Get tenant by ID
     */
    public function getTenantById(string $id): TenantDTO
    {
        $tenant = $this->tenantRepository->findOrFail($id);
        return TenantDTO::fromModel($tenant);
    }

    /**
     * Create new tenant with admin user
     */
    public function createTenant(CreateTenantDTO $dto): TenantDTO
    {
        // Create tenant
        $tenant = $this->tenantRepository->create([
            'id' => Str::uuid()->toString(),
            'name' => $dto->companyName,
            'email' => $dto->companyEmail,
        ]);

        // Create domain for tenant
        $tenant->domains()->create([
            'domain' => $dto->domain,
        ]);

        // Create admin user in tenant context
        $tenant->run(function () use ($dto) {
            // Explicitly use the tenant connection for user creation
            $admin = User::on('mysql')->create([
                'name' => $dto->adminName,
                'email' => $dto->adminEmail,
                'password' => Hash::make($dto->adminPassword),
                'role' => 'admin',
            ]);
        });

        return TenantDTO::fromModel($tenant->load('domains'));
    }

    /**
     * Delete tenant
     */
    public function deleteTenant(string $id): bool
    {
        return $this->tenantRepository->delete($id);
    }
}
