<?php

namespace Rbac\Controllers;

use Rbac\Filters\AdminUserFilter;
use Rbac\Requests\AdminUserProfileRequest;
use Rbac\Requests\AdminUserRequest;
use Rbac\Resources\AdminUserResource;
use Rbac\Models\AdminPermission;
use Rbac\Models\AdminUser;
use Rbac\Requests\AdminUserPasswordRequest;
use Rbac\Utils\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{

    public function __construct()
    {
        $this->user = app(config('auth.providers.admin.model'));
    }

    public function user()
    {
        $user = Admin::user();

        return $this->ok(AdminUserResource::make($user)->for(AdminUserResource::FOR_INFO))->wrap();
    }

    public function editUser()
    {
        $user = Admin::user();
        return $this->ok(AdminUserResource::make($user)->for(AdminUserResource::FOR_EDIT_INFO))->wrap();
    }

    public function updateUser(AdminUserProfileRequest $request)
    {
        $inputs = $request->validated();
        Admin::user()->updateUser($inputs);
        return $this->created(AdminUserResource::make(Admin::user()))->wrap();
    }

    public function index(AdminUserFilter $filter)
    {
        $admin = new AdminUser;
        $users = $admin->query()
            ->filter($filter)
            ->with(['roles', 'permissions'])
            ->orderByDesc($admin->getKeyName())
            ->paginate();

        return $this->ok(AdminUserResource::forCollection(AdminUserResource::FOR_INDEX, $users->items())->additional(['total' => $users->total()]))->wrap();
    }

    public function store(AdminUserRequest $request, $user)
    {
        $inputs = $request->validated();

        $user = $this->user::createUser($inputs);

        if (!empty($q = $request->post('roles', []))) {
            $user->roles()->attach($q);
        }
        if (!empty($q = $request->post('permissions', []))) {
            $user->permissions()->attach($q);
        }

        return $this->created()->wrap();
    }

    public function show($adminUser)
    {
        $adminUser->load(['roles', 'permissions']);

        return $this->ok(AdminUserResource::make($adminUser))->wrap();
    }

    public function update(AdminUserRequest $request, $adminUser)
    {
        $inputs = $request->validated();

        $adminUser = $this->user->findOrFail($adminUser);
        $adminUser->updateUser($inputs);

        if (isset($inputs['roles'])) {
            $adminUser->roles()->sync($inputs['roles']);
        }
        if (isset($inputs['permissions'])) {
            $adminUser->permissions()->sync($inputs['permissions']);
        }
        return $this->created(AdminUserResource::make($adminUser)->for(AdminUserResource::FOR_EDIT))->wrap();
    }

    public function destroy($adminUser)
    {
        $adminUser = $this->user->findOrFail($adminUser);
        $adminUser->delete();

        return $this->ok()->wrap();
    }

    public function edit(Request $request, $adminUser)
    {
        return $this->ok(
            AdminUserResource::make($this->user->findOrFail($adminUser))
                ->for(AdminUserResource::FOR_EDIT)
                ->additional($this->formData())
        )->wrap();
    }

    /**
     * 返回创建和编辑表单所需的选项数据
     *
     * @return array
     */
    protected function formData()
    {
        $role_query = new Admin;
        $per_query = new AdminPermission;
        $roles = $query->query()
            ->orderByDesc($role_query->getKeyName())
            ->get();
        $permissions = $per_query->query()
            ->orderByDesc($per_query->getKeyName())
            ->get();

        return compact('roles', 'permissions');
    }

    public function create()
    {
        return $this->ok($this->formData())->wrap();
    }

    /**
     * 修改用户密码
     *
     * @param AdminUserPasswordRequest $request
     */
    public function updatePassword(AdminUserPasswordRequest $request)
    {
        $user = Admin::user();
        $inputs = $request->validated();
        if (Hash::check($inputs['old_password'], $user->password)) {
            $user->password = bcrypt($inputs['password']);
            $user->save();
        } else {
            return response()->json(['errors' => ['old_password' => ['旧密码不正确，请重新输入']]], 422)->wrap();
        }

        return $this->noContent()->wrap();
    }

    /**
     * 更新用户个人设置信息
     *
     * @param Request $request
     */
    public function updateProfile(Request $request)
    {
        $user = Admin::user();
        $user->personal = $request->all();
        $user->save();

        return $this->ok($user->personal)->wrap();
    }

    /**
     * 获取用户对应组的角色
     */
    public function roles()
    {
        return $this->ok(Admin::user()->roles)->wrap();
    }
}
