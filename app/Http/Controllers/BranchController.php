<?php

namespace App\Http\Controllers;

use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Http\Requests\Branch\UpdateBranchStatusRequest;
use App\Http\Resources\Branch\BranchResource;
use App\Services\Audit\AuditService;
use App\Services\Branch\BranchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    use ApiResponse;

    public function __construct(
        private BranchService $branches,
        private AuditService $audit,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $branches = $this->branches->list($request->integer('per_page', 1));

        return $this->success(
            message: 'Branches retrieved successfully.',
            data: BranchResource::collection($branches),
        );
    }

    public function show(int $id): JsonResponse
    {
        $branch = $this->branches->find($id);

        return $this->success(
            message: 'Branch retrieved successfully.',
            data: new BranchResource($branch),
        );
    }

    public function store(StoreBranchRequest $request): JsonResponse
    {
        try {
            $branch = $this->branches->create($request->validated());

            $this->audit->log(
                action: 'created',
                module: 'branches',
                auditable: $branch,
                after: $branch->toArray(),
                description: "Branch '{$branch->name}' ({$branch->code}) created.",
            );

            return $this->success(
                message: 'Branch created successfully.',
                data: new BranchResource($branch),
                responseCode: '000',
                statusCode: 201,
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->error(
                message: app()->isLocal() ? $e->getMessage() : 'Could not create branch.',
                responseCode: '100',
                statusCode: 500,
            );
        }
    }

    public function update(UpdateBranchRequest $request, int $id): JsonResponse
    {
        try {
            $branch = $this->branches->find($id);
            $before = $branch->toArray();

            $branch = $this->branches->update($branch, $request->validated());

            $this->audit->log(
                action: 'updated',
                module: 'branches',
                auditable: $branch,
                before: $before,
                after: $branch->toArray(),
                description: "Branch '{$branch->name}' ({$branch->code}) updated.",
            );

            return $this->success(
                message: 'Branch updated successfully.',
                data: new BranchResource($branch),
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->error(
                message: app()->isLocal() ? $e->getMessage() : 'Could not update branch.',
                responseCode: '100',
                statusCode: 500,
            );
        }
    }


    public function updateStatus(UpdateBranchStatusRequest $request, int $id): JsonResponse
    {
        try {
            $branch = $this->branches->find($id);
            $before = $branch->status;

            $branch = $this->branches->updateStatus($branch, $request->validated()['status']);

            $this->audit->log(
                action: 'status_updated',
                module: 'branches',
                auditable: $branch,
                before: ['status' => $before],
                after: ['status' => $branch->status],
                description: "Branch '{$branch->name}' ({$branch->code}) status changed from '{$before}' to '{$branch->status}'.",
            );

            return $this->success(
                message: 'Branch status updated successfully.',
                data: new BranchResource($branch),
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->error(
                message: 'Unable to complete this request. Please try again.',
                responseCode: '100',
                statusCode: 500,
            );
        }
    }
}
