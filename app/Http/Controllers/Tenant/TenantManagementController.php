<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Resources\Tenant\TenantResource;
use App\Mail\CoreBankMail;
use App\Services\Audit\AuditService;
use App\Services\Tenant\TenantProvisionerService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class TenantManagementController extends Controller
{
    use ApiResponse;

    public function __construct(
        private TenantProvisionerService $provisioner,
        private AuditService $audit,
    ) {}

    public function store(StoreTenantRequest $request): JsonResponse
    {
        try {
            $tenant = $this->provisioner->create($request->validated());

            $this->audit->log(
                action: 'created',
                module: 'tenants',
                auditable: $tenant,
                after: $tenant->only(['id', 'code', 'name', 'type', 'country']),
                description: "Tenant '{$tenant->name}' ({$tenant->code}) created.",
            );

            Mail::to($tenant->email)->send(new CoreBankMail(
                subjectLine: 'Welcome to ' . config('app.name') . ' — Your Workspace is Ready',
                content: view('emails.bodies.tenant.created', ['tenant' => $tenant])->render(),
            ));
            return $this->success(
                message: 'Tenant created successfully.',
                data: new TenantResource($tenant),
                responseCode: '000',
                statusCode: 201,
            );
        } catch (\Throwable $e) {
            report($e);

            $this->audit->log(
                action: 'create_failed',
                module: 'tenants',
                description: 'Tenant creation failed: ' . $e->getMessage(),
            );

            return $this->error(
                message: app()->isLocal() ? $e->getMessage() : 'Could not create tenant.',
                responseCode: '100',
                statusCode: 500,
            );
        }
    }
}
