<?php

namespace Rbac\Resources;

/**
 * @mixin \Rbac\Models\AdminRole
 */
class AdminRoleResource extends JsonResource
{
    public const FOR_INDEX = 'index';
    public const FOR_EDIT = 'edit';

    public function toArray($request)
    {
        return [
            $this->getKeyName() => $this->getKey(),
            'name' => $this->name,
            'slug' => $this->slug,
            'routerPermissions' => $this->routers()->pluck($this->getKeyName()),
            $this->mergeFor(static::FOR_INDEX, function () {
                return [
                    'permissions' => $this->permissions()->select([$this->getKeyName(), 'name'])->get(),
                ];
            }),
            $this->mergeFor(static::FOR_EDIT, function () {
                return [
                    'permissions' => $this->permissions()->select([$this->getKeyName(), 'name'])->get(),
                ];
            }),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
