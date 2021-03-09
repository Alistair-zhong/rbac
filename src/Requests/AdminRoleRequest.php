<?php

namespace Rbac\Requests;

use Rbac\Models\AdminRole;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Rbac\Models\AdminPermission;

class AdminRoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('admin_role');
        $adminRole = new AdminRole;
        $permission = new AdminPermission;


        $rules = [
            'name' => ['required', Rule::unique('admin_roles', 'name')],
            'slug' => ['required', Rule::unique('admin_roles', 'slug')],
            'permissions' => 'array',
            'permissions.*' => 'exists:admin_permissions,' . $permission->getKeyName(),
        ];

        if ($this->isMethod('put')) {
            $role = $adminRole->findOrFail($id);
            $rules = array_merge(
                $rules,
                $rules = [
                    'name' => ['required', Rule::unique('admin_roles', 'name')->ignore($role->getKey(), $adminRole->getKeyName())],
                    'slug' => ['required', Rule::unique('admin_roles', 'slug')->ignore($role->getKey(), $adminRole->getKeyName())],
                ]
            );

            $rules = Arr::only($rules, $this->keys());
        }
        return $rules;
    }

    public function attributes()
    {
        return [
            'name' => '名称',
            'slug' => '标识',
            'permissions' => '权限',
            'permissions.*' => '权限',
        ];
    }
}
