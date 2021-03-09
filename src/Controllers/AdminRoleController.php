<?php

namespace Rbac\Controllers;

use Rbac\Filters\AdminRoleFilter;
use Rbac\Requests\AdminRoleRequest;
use Rbac\Resources\AdminRoleResource;
use Rbac\Models\AdminPermission;
use Rbac\Models\AdminRole;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    public function store(AdminRoleRequest $request, AdminRole $model)
    {
        $inputs = $request->validated();
        $role = $model->create($inputs);

        if (!empty($perms = $inputs['permissions'] ?? [])) {
            $role->permissions()->attach($perms);
        }

        return $this->created(AdminRoleResource::make($role));
    }

    public function edit($adminRole)
    {
        return $this->ok(
            AdminRoleResource::make(AdminRole::find($adminRole))
                ->for(AdminRoleResource::FOR_EDIT)
                ->additional($this->formData())
        );
    }

    public function update(AdminRoleRequest $request, $adminRole)
    {
        $inputs = $request->validated();
        $adminRole = AdminRole::findOrFail($adminRole);
        $adminRole->update($inputs);

        if (isset($inputs['permissions'])) {
            $adminRole->permissions()->sync($inputs['permissions']);
        }
        return $this->created(AdminRoleResource::make($adminRole));
    }

    public function destroy($adminRole)
    {
        AdminRole::find($adminRole)->delete();

        return $this->noContent();
    }

    public function index(Request $request, AdminRoleFilter $filter)
    {
        $model = new AdminRole;
        $roles = $model->query()
            ->with(['permissions'])
            ->filter($filter)
            ->orderByDesc($model->getKeyName());

        $roles = $request->get('all') ? $roles->get() : $roles->paginate();

        return $this->ok(AdminRoleResource::forCollection(AdminRoleResource::FOR_INDEX, $roles));
    }

    /**
     * 返回添加和编辑表单所需的选项数据
     *
     * @return array
     */
    protected function formData()
    {
        $model = new AdminPermission;
        $permissions = $model->query()
            ->select([$model->getKeyName(), 'name'])
            ->orderByDesc($model->getKeyName())
            ->get();

        return compact('permissions');
    }

    public function create()
    {
        return $this->ok($this->formData());
    }
}
