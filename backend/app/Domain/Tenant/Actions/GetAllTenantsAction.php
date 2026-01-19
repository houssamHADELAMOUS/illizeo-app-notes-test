<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Tenant\Services\TenantService;

class GetAllTenantsAction
{
    public function __construct(
        private TenantService $tenantService
    ) {}

    public function execute(): array
    {
        return $this->tenantService->getAllTenants();
    }
}
