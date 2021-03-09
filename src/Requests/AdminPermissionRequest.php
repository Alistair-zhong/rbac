<?php

namespace Rbac\Requests;

use Rbac\Models\AdminPermission;
use Rbac\Rules\AdminPermissionHttpPath;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class AdminPermissionRequest extends FormRequest
{
    public function rules()
    {
        $id = $this->route('admin_permission');
        $permission = AdminPermission::findOrFail($id);

        $rules = [
            'name' => ['required', Rule::unique('admin_permissions', 'name')->ignore($permission->getKey(), $permission->getKeyName())],
            'slug' => ['required', Rule::unique('admin_permissions', 'slug')->ignore($permission->getKey(), $permission->getKeyName())],
            'http_method' => 'nullable|array',
            'http_method.*' => Rule::in(AdminPermission::$httpMethods),
            'http_path' => [
                'nullable',
                new AdminPermissionHttpPath(),
            ],
        ];
        if ($this->isMethod('put')) {
            $rules = Arr::only($rules, $this->keys());
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'name' => '名称',
            'slug' => '标识',
            'http_method' => '请求方法',
            'http_method.*' => '请求方法',
            'http_path' => '请求地址',
        ];
    }
}
