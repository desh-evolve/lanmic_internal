<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * The direct permissions that belong to the user.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    /**
     * Check if user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has any of the given roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    // /**
    //  * Check if user has a specific permission.
    //  *
    //  * @param string $permission
    //  * @return bool
    //  */
    // public function hasPermission($permission)
    // {
    //     // Check direct user permissions first
    //     if ($this->permissions()->where('name', $permission)->exists()) {
    //         return true;
    //     }

    //     foreach ($this->roles as $role) {
    //         if ($role->permissions()->where('name', $permission)->exists()) {
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // /**
    //  * Check if user has any of the given permissions.
    //  *
    //  * @param array $permissions
    //  * @return bool
    //  */
    // public function hasAnyPermission($permissions)
    // {
    //     // Check direct user permissions first
    //     if ($this->permissions()->whereIn('name', $permissions)->exists()) {
    //         return true;
    //     }


    //     foreach ($this->roles as $role) {
    //         if ($role->permissions()->whereIn('name', $permissions)->exists()) {
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // /**
    //  * Check if user has all of the given permissions.
    //  *
    //  * @param array $permissions
    //  * @return bool
    //  */
    // public function hasAllPermissions($permissions)
    // {
    //     foreach ($permissions as $permission) {
    //         if (!$this->hasPermission($permission)) {
    //             return false;
    //         }
    //     }
    //     return true;
    // }
    /**
     * Get all permissions (both direct and from roles).
     *
     * @return \Illuminate\Support\Collection
     */
    // public function getAllPermissions()
    // {
    //     // Get direct permissions
    //     $directPermissions = $this->permissions;

    //     // Get permissions from roles
    //     $rolePermissions = $this->roles->flatMap(function ($role) {
    //         return $role->permissions;
    //     });

    //     // Merge and remove duplicates
    //     return $directPermissions->merge($rolePermissions)->unique('id');
    // }

    public function hasPermission($permission)
    {
        // Check ONLY direct user permissions - ignore role permissions
        return $this->permissions()->where('name', $permission)->exists();
    }

    // Also update hasAnyPermission to only check direct permissions
    public function hasAnyPermission($permissions)
    {
        // Check ONLY direct user permissions
        return $this->permissions()->whereIn('name', $permissions)->exists();
    }

    // Update hasAllPermissions to only check direct permissions
    public function hasAllPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->permissions()->where('name', $permission)->exists()) {
                return false;
            }
        }
        return true;
    }

    // Update getAllPermissions to only return direct permissions
    public function getAllPermissions()
    {
        // Return ONLY direct permissions
        return $this->permissions;
    }
    /**
     * Assign a permission directly to the user.
     *
     * @param string|Permission $permission
     * @return void
     */
    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    /**
     * Revoke a direct permission from the user.
     *
     * @param string|Permission $permission
     * @return void
     */
    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }
    }

    /**
     * Sync direct permissions for the user.
     *
     * @param array $permissions
     * @return void
     */
    public function syncPermissions($permissions)
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('name', $permission)->firstOrFail()->id;
            }
            return is_object($permission) ? $permission->id : $permission;
        });

        $this->permissions()->sync($permissionIds);
    }

    /**
     * Get the requisitions for the user.
     */
    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }
}
