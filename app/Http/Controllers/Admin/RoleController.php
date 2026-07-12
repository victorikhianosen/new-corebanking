<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\SyncRolePermissionsRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Role\RoleResource;
use App\Services\Role\RoleService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private RoleService $roles,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $roles = $this->roles->list($request->integer('per_page', 15));

        return $this->success(
            message: 'Roles retrieved successfully.',
            data: RoleResource::collection($roles),
        );
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->roles->find($id);

        return $this->success(
            message: 'Role retrieved successfully.',
            data: new RoleResource($role),
        );
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $role = $this->roles->create($request->validated());

            return $this->success(
                message: 'Role created successfully.',
                data: new RoleResource($role),
                responseCode: '000',
                statusCode: 201,
            );
        } catch (ValidationException $e) {
            return $this->error(
                message: $e->getMessage(),
                responseCode: '101',
                statusCode: 422,
                errors: $e->errors(),
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->error(
                message: 'We are unable to process your request please try again.',
                responseCode: '500',
                statusCode: 500,
            );
        }
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        try {
            $role = $this->roles->find($id);
            $role = $this->roles->update($role, $request->validated());

            return $this->success(
                message: 'Role updated successfully.',
                data: new RoleResource($role),
            );
        } catch (ModelNotFoundException $e) {
            return $this->error(
                message: 'The requested role was not found.',
                responseCode: '404',
                statusCode: 404,
            );
        } catch (ValidationException $e) {
            return $this->error(
                message: $e->getMessage(),
                responseCode: '101',
                statusCode: 422,
                errors: $e->errors(),
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->error(
                message: 'We are unable to process your request please try again.',
                responseCode: '500',
                statusCode: 500,
            );
        }
    }


    public function destroy(int $id): JsonResponse
{
    try {
        $this->roles->delete($id);

        return $this->success(
            message: 'Role deleted successfully.',
        );
    } catch (ModelNotFoundException $e) {
        return $this->error(
            message: 'The requested role was not found.',
            responseCode: '404',
            statusCode: 404,
        );
    } catch (\Throwable $e) {
        report($e);

        return $this->error(
            message: 'We are unable to delete the role. Please try again.',
            responseCode: '500',
            statusCode: 500,
        );
    }
}

    public function syncPermissions(SyncRolePermissionsRequest $request, int $id): JsonResponse
    {
        try {
            $role = $this->roles->find($id);
            $role = $this->roles->syncPermissions($role, $request->validated()['permissions']);

            return $this->success(
                message: 'Role permissions updated successfully.',
                data: new RoleResource($role),
            );
        } catch (ModelNotFoundException $e) {
            return $this->error(
                message: 'The requested role was not found.',
                responseCode: '404',
                statusCode: 404,
            );
        } catch (ValidationException $e) {
            return $this->error(
                message: $e->getMessage(),
                responseCode: '101',
                statusCode: 422,
                errors: $e->errors(),
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->error(
                message: 'We are unable to process your request please try again.',
                responseCode: '500',
                statusCode: 500,
            );
        }
    }
}
