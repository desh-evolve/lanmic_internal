<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name','module', 'slug', 'description'];

    /**
     * The roles that belong to the permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

     /**
     * The users that have this permission directly assigned.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_user');
    }
}