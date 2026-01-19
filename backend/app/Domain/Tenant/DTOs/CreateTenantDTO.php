<?php

namespace App\Domain\Tenant\DTOs;

class CreateTenantDTO
{
    public function __construct(
        public readonly string $companyName,
        public readonly string $companyEmail,
        public readonly string $domain,
        public readonly string $adminName,
        public readonly string $adminEmail,
        public readonly string $adminPassword,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            companyName: $data['company_name'],
            companyEmail: $data['company_email'],
            domain: $data['domain'],
            adminName: $data['admin_name'],
            adminEmail: $data['admin_email'],
            adminPassword: $data['admin_password'],
        );
    }
}
