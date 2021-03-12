<?php

namespace Rbac\Controllers;

use Rbac\Filters\AdminPermissionFilter;
use Rbac\Requests\AdminPermissionRequest;
use Rbac\Resources\AdminPermissionResource;
use Rbac\Models\AdminPermission;
use Illuminate\Http\Request;

class AdminPermissionController extends Controller
{
    public function store(AdminPermissionRequest $request, AdminPermission $model)
    {
        $inputs = $request->validated();

        $res = $model->create($inputs);
        return $this->created(AdminPermissionResource::make($res));
    }

    public function index(Request $request, AdminPermissionFilter $filter)
    {
        $model = new AdminPermission;
        $perms = $model->query()
            ->filter($filter)
            ->orderByDesc($model->getKeyName());
        $perms = $request->get('all') ? $perms->get() : $perms->paginate();

        return $this->ok(AdminPermissionResource::collection($perms->items())->additional(['total' => $perms->total()]));
    }

    public function edit($adminPermission)
    {
        return $this->ok(AdminPermissionResource::make(AdminPermission::findOrFail($adminPermission)));
    }

    public function update(AdminPermissionRequest $request, $adminPermission)
    {
        $inputs = $request->validated();
        $adminPermission = AdminPermission::findOrFail($adminPermission);
        $adminPermission->update($inputs);

        return $this->created(AdminPermissionResource::make($adminPermission));
    }

    public function destroy($adminPermission)
    {
        $adminPermission = AdminPermission::findOrFail($adminPermission);
        $adminPermission->delete();

        return $this->noContent();
    }
}
