<?php

namespace Rbac\Controllers;

use Rbac\Utils\Admin;
use Rbac\Enums\RoleSlug;
use Rbac\Models\AdminRole;
use Illuminate\Http\Request;
use Rbac\Models\AdminPermission;
use Rbac\Filters\AdminRoleFilter;
use Rbac\Requests\AdminRoleRequest;
use Rbac\Resources\AdminRoleResource;

class AdminRoleController extends Controller
{
    public function store(AdminRoleRequest $request, AdminRole $model)
    {
        $inputs = $request->validated();
        $role = $model->create($inputs);

        if (!empty($perms = $inputs['permissions'] ?? [])) {
            $role->permissions()->attach($perms);
        }

        return $this->created(AdminRoleResource::make($role))->wrap();
    }

    public function edit($adminRole)
    {
        return $this->ok(
            AdminRoleResource::make(AdminRole::find($adminRole))
                ->for(AdminRoleResource::FOR_EDIT)
                ->additional($this->formData())
        )->wrap();
    }

    public function update(AdminRoleRequest $request, $adminRole)
    {
        $inputs = $request->validated();
        $adminRole = AdminRole::findOrFail($adminRole);
        $adminRole->update($inputs);

        if (isset($inputs['permissions'])) {
            $adminRole->permissions()->sync($inputs['permissions']);
        }
        return $this->created(AdminRoleResource::make($adminRole))->wrap();
    }

    public function destroy($adminRole)
    {
        $adminRole = AdminRole::find($adminRole);
        if ($adminRole) {
            $adminRole->delete();
        }

        return $this->ok()->wrap();
    }

    public function index(Request $request, AdminRoleFilter $filter)
    {
        // todo 排除超级管理员， 如果当前角色是非超级管理，还需排除组长角色
        $model = new AdminRole;
        $except_roles = Admin::isSuperAdmin() ? [RoleSlug::SuperAdmin] : [RoleSlug::SuperAdmin, RoleSlug::Leader];

        $roles = $model->query()
            ->whereNotIn('slug', $except_roles)
            ->with(['permissions'])
            ->filter($filter)
            ->orderByDesc($model->getKeyName());

        $roles = $request->get('all') ? $roles->get() : $roles->paginate();

        return $this->ok(AdminRoleResource::forCollection(AdminRoleResource::FOR_INDEX, $roles->items())->additional(['total' => $roles->total()]))->wrap();
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
        return $this->ok($this->formData())->wrap();
    }

    /**
     * 批量更新角色 权限 路由方法
     */
    public function bulkUpdate(Request $request)
    {
        $query = new AdminRole;
        try {
            foreach ($request->all() as $item) {

                $role = $query->where('slug', $item['slug'])->first();

                $role->permissions()->sync(array_column($item['permissions'], $query->getKeyName()));

                $role->routers()->sync($item['routerPermissions']);
            }

            return $this->ok()->wrap();
        } catch (\Throwable $th) {
            return $this->error('更新失败' . $th->getMessage())->wrap();
        }
    }
}
