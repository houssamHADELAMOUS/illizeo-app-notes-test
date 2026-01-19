<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Tenant\DTOs\CreateTenantDTO;
use App\Domain\Tenant\DTOs\TenantDTO;
use App\Domain\Tenant\Services\TenantService;

class CreateTenantAction
{
    public function __construct(
        private TenantService $tenantService
    ) {}

    public function execute(CreateTenantDTO $dto): TenantDTO
    {
        return $this->tenantService->createTenant($dto);
    }
}
