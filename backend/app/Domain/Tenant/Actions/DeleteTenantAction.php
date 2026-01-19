<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Tenant\Services\TenantService;

class DeleteTenantAction
{
    public function __construct(
        private TenantService $tenantService
    ) {}

    public function execute(string $id): bool
    {
        return $this->tenantService->deleteTenant($id);
    }
}
