<?php

namespace Rbac\Resources;

use Rbac\Models\Dict;
use Rbac\Utils\Admin;
use Minishlink\WebPush\VAPID;

/**
 * @mixin \Rbac\Models\AdminUser
 */
class AdminUserResource extends JsonResource
{
    public const FOR_INFO = 'info';
    public const FOR_EDIT_INFO = 'edit_info';
    public const FOR_EDIT = 'edit';
    public const FOR_INDEX = 'index';

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            $this->getKeyName()      => $this->getKey(),
            'username' => $this->username,
            'name'     => $this->name,
            'avatar'   => $this->avatar,

            $this->mergeFor(static::FOR_INFO, function () {

                return [
                    'roles' => $this->roles()->pluck('slug'),
                    'permissions' => $this->allPermissions()->pluck('slug')
                ];
            }),
            $this->mergeFor(static::FOR_EDIT, function () {
                $keyName = $this->getKeyName();
                return [
                    'roles' => $this->roles()->pluck($keyName),
                    'permissions' => $this->permissions()->pluck($keyName),
                ];
            }),
            $this->mergeFor(static::FOR_EDIT_INFO, function () {
                return [
                    'roles' => $this->roles()->pluck('name'),
                    'permissions' => $this->permissions()->pluck('name'),
                ];
            }),
            $this->mergeFor(static::FOR_INDEX, function () {
                return [
                    'roles' => $this->roles()->pluck('name'),
                    'permissions' => $this->permissions->pluck('name'),
                ];
            }),

        ];
    }
}
