<?php

namespace Rbac\Controllers;

use Rbac\Exceptions\VueRouterException;
use Rbac\Requests\VueRouterRequest;
use Rbac\Resources\VueRouterResource;
use Rbac\Models\AdminPermission;
use Rbac\Models\AdminRole;
use Rbac\Models\VueRouter;
use Illuminate\Http\Request;

class VueRouterController extends Controller
{
    public function store(VueRouterRequest $request, VueRouter $vueRouter)
    {
        $inputs = $request->validated();

        $vueRouter = $vueRouter->create($inputs);
        if (!empty($q = $request->post('roles', []))) {
            $vueRouter->roles()->attach($q);
        }
        return $this->created(VueRouterResource::make($vueRouter))->wrap();
    }

    public function update(VueRouterRequest $request, $vueRouter)
    {
        $inputs = $request->validated();
        $vueRouter = VueRouter::findOrFail($vueRouter);
        $vueRouter->update($inputs);

        if (isset($inputs['roles'])) {
            $vueRouter->roles()->sync($inputs['roles']);
        }

        return $this->created(VueRouterResource::make($vueRouter))->wrap();
    }

    public function edit($vueRouter)
    {
        $vueRouter = VueRouter::findOrFail($vueRouter);

        return $this->ok(
            VueRouterResource::make($vueRouter)
                ->for(VueRouterResource::FOR_EDIT)
                ->additional($this->formData($vueRouter->getKey()))
        )->wrap();
    }

    public function index(Request $request, VueRouter $vueRouter)
    {

        return $this->ok($vueRouter->treeExcept((int) $request->input('except'))->toTree())->wrap();
    }

    public function destroy($vueRouter)
    {
        $vueRouter = VueRouter::findOrFail($vueRouter);

        $vueRouter->delete();
        return $this->ok()->wrap();
    }

    public function batchUpdate(Request $request, VueRouter $vueRouter)
    {
        $vueRouter->saveOrder($request->input('_order', []));

        return $this->created()->wrap();
    }

    /**
     * 返回添加和编辑表单时用到的选项数据
     *
     * @param int $exceptRouterId 要排除的 路由配置 id，编辑表单用到
     *
     * @return array
     */
    protected function formData($exceptRouterId = null)
    {
        $model = app(VueRouter::class);
        $role_model = new AdminRole;
        $permission = new AdminPermission;

        $vueRouters = $model->treeExcept($exceptRouterId ?? 0)->toTree();

        $roles = $role_model->query()
            ->orderByDesc($role_model->getKeyName())
            ->get();

        $permissions = $permission->query()
            ->orderByDesc($permission->getKeyName())
            ->get();

        return [
            'vue_routers' => $vueRouters,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }

    public function create()
    {
        return $this->ok($this->formData())->wrap();
    }

    public function importVueRouters(VueRouterRequest $request, VueRouter $vueRouter)
    {
        $file = $request->file('file');

        try {
            $vueRouters = $vueRouter->replaceFromFile($file);
            return $this->created($vueRouters)->wrap();
        } catch (VueRouterException $e) {
            return $this->error($e->getMessage())->wrap();
        }
    }
}
