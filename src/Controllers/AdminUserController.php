<?php

namespace Rbac\Controllers;

use Rbac\Enums\RoleSlug;
use Rbac\Filters\AdminUserFilter;
use Rbac\Requests\AdminUserProfileRequest;
use Rbac\Requests\AdminUserRequest;
use Rbac\Resources\AdminUserResource;
use Rbac\Models\AdminPermission;
use Rbac\Models\AdminUser;
use Rbac\Models\SystemMedia;
use Rbac\Requests\AdminUserPasswordRequest;
use Rbac\Requests\SystemMediaRequest;
use Rbac\Utils\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{

    public function user()
    {
        $user = Admin::user();

        return $this->ok(AdminUserResource::make($user)->for(AdminUserResource::FOR_INFO));
    }

    public function editUser()
    {
        $user = Admin::user();
        return $this->ok(AdminUserResource::make($user)->for(AdminUserResource::FOR_EDIT_INFO));
    }

    public function updateUser(AdminUserProfileRequest $request)
    {
        $inputs = $request->validated();
        Admin::user()->updateUser($inputs);
        return $this->created(AdminUserResource::make(Admin::user()));
    }

    public function index(AdminUserFilter $filter)
    {
        $admin = new AdminUser;
        $users = $admin->query()
            ->filter($filter)
            ->with(['roles', 'permissions'])
            ->orderByDesc($admin->getKeyName())
            ->paginate();

        return $this->ok(AdminUserResource::forCollection(AdminUserResource::FOR_INDEX, $users));
    }

    public function store(AdminUserRequest $request, AdminUser $user)
    {
        $inputs = $request->validated();
        $user = $user::createUser($inputs);

        if (!empty($q = $request->post('roles', []))) {
            $user->roles()->attach($q);
        }
        if (!empty($q = $request->post('permissions', []))) {
            $user->permissions()->attach($q);
        }

        return $this->created();
    }

    public function show(AdminUser $adminUser)
    {
        $adminUser->load(['roles', 'permissions']);

        return $this->ok(AdminUserResource::make($adminUser));
    }

    public function update(AdminUserRequest $request, AdminUser $adminUser)
    {
        $inputs = $request->validated();
        $adminUser->updateUser($inputs);
        if (isset($inputs['roles'])) {
            $adminUser->roles()->sync($inputs['roles']);
        }
        if (isset($inputs['permissions'])) {
            $adminUser->permissions()->sync($inputs['permissions']);
        }
        return $this->created(AdminUserResource::make($adminUser)->for(AdminUserResource::FOR_EDIT));
    }

    public function destroy(AdminUser $adminUser)
    {
        $adminUser->delete();
        return $this->noContent();
    }

    public function edit(Request $request, AdminUser $adminUser)
    {
        return $this->ok(
            AdminUserResource::make($adminUser)
                ->for(AdminUserResource::FOR_EDIT)
                ->additional($this->formData())
        );
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
        return $this->ok($this->formData());
    }

    //acs 添加,202011
    /**
     * 根据用户id获取用户信息
     *
     * @param AdminUser $userId
     *
     * @return Json
     */
    public function info(AdminUser $userId)
    {
        $role = $userId->isAdministrator() ? RoleSlug::Administrator : $userId->activeGroup()->role->slug;
        $user = [
            'name'   => $userId->name,
            'avatar' => url((string) $userId->avatar),
            'role'   => $role,
        ];

        return $this->ok($user);
    }

    /**
     * 更新用户图像
     *
     * @param SystemMediaRequest $request
     */
    public function avatar(SystemMediaRequest $request)
    {
        $user = Admin::user();
        $files = $this->saveFiles($request, 'avatar');
        if ($user->avatar && $files['file']['path'] !== $user->avatar) {
            $systemMedia = SystemMedia::where('path', $user->avatar)->first();
            $systemMedia->delete();
        }
        $file = SystemMedia::create($files['file']);
        $user->avatar = $file->path;
        $user->save();

        $avatar = [
            'id' => $file->id,
            'url' => url($file->path),
            'status' => 'done'
        ];

        return $this->created($avatar);
    }

    /**
     * 获取个人基本信息
     */
    public function profile()
    {
        $user = Admin::user();

        $profile = [
            'basic_settings' => [
                'account'   => $user->username,
                'name'      => $user->name,
                'country'   => config('countries.' . $user->country) . '(' . $user->country . ')',
                'group'     => $user->activeGroup() ? $user->activeGroup()->name : '',
                'join_date' => $user->join_date,
                'avatar'    => url($user->avatar),
            ],
            'preferences' => $user->getPersonal(),

        ];

        return $this->ok($profile);
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
            return response()->json(['errors' => ['old_password' => ['旧密码不正确，请重新输入']]], 422);
        }

        return $this->noContent();
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

        return $this->ok($user->personal);
    }

    /**
     * 获取用户对应组的角色
     */
    public function roles()
    {
        return $this->ok(Admin::user()->roles);
    }
}
