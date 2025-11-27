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

    /**
     * Check if user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('name', $permission)->exists()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has any of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission($permissions)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions()->whereIn('name', $permissions)->exists()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the requisitions for the user.
     */
    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }
}