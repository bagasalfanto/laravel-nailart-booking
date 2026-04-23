<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class SidebarMenu extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'title',
        'icon',
        'route_name',
        'url',
        'permission_name',
        'sort_order',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'parent_id' => 'string',
            'title' => 'string',
            'icon' => 'string',
            'route_name' => 'string',
            'url' => 'string',
            'permission_name' => 'string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Set the cache prefix.
     */
    public function setCachePrefix(): string
    {
        return 'sidebar_menu.cache';
    }

    /**
     * Get parent menu.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    /**
     * Get child menus.
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->orderBy('sort_order');
    }

    /**
     * Roles allowed to see this menu.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'sidebar_menu_role', 'menu_id', 'role_id');
    }
}
