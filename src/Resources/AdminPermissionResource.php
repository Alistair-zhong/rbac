<?php

namespace Rbac\Resources;

/**
 * @mixin \Rbac\Models\AdminPermission
 */
class AdminPermissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            $this->getKeyName() => $this->getKey(),
            'name' => $this->name,
            'slug' => $this->slug,
            'http_method' => $this->http_method,
            'http_path' => $this->http_path,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
