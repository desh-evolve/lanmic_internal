<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('permission:view-users')->only(['index', 'show']);
    //     $this->middleware('permission:create-users')->only(['create', 'store']);
    //     $this->middleware('permission:edit-users')->only(['edit', 'update','userPermission']);
    //     $this->middleware('permission:delete-users')->only('destroy');
    // }


    public function __construct()
    {
        // Apply DIRECT permission middleware to specific methods
        $this->middleware(CheckPermission::class . ':view-users')
            ->only(['index', 'show']);

        $this->middleware(CheckPermission::class . ':create-users')
            ->only(['create', 'store']);

        $this->middleware(CheckPermission::class . ':edit-users')
            ->only(['edit', 'update', 'userPermission']);

        $this->middleware(CheckPermission::class . ':delete-users')
            ->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->roles()->attach($request->roles);

           // Get permissions through roles (assuming Role model has permissions relationship)
    $permissionIds = [];
    foreach ($request->roles as $roleId) {
        $role = Role::find($roleId);
        $rolePermissionIds = $role->permissions()->pluck('permission_id')->toArray();
        $permissionIds = array_merge($permissionIds, $rolePermissionIds);
    }
   // Remove duplicates
    $permissionIds = array_unique($permissionIds);
    
    // Attach permissions to user
    $user->permissions()->attach($permissionIds);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('roles.permissions');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $user->roles()->sync($request->roles);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

   public function userPermission(Request $request, User $user)
{
    // Group permissions by module
    $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

    // Get ONLY ACTIVE permission IDs for this user
    $userPermissions = DB::table('permission_user')
        ->where('user_id', $user->id)
        ->where('status', 'active')
        ->pluck('permission_id')
        ->toArray();

    return view('admin.users.user_permission', compact('permissions', 'user', 'userPermissions'));
}

    public function updateUserPermission(Request $request, User $user)
    {
        $permissions = $request->input('permissions', []);

        if (empty($permissions)) {
            return redirect()->back()
                ->with('error', 'Please select at least one permission.');
        }

        // Get currently active permissions
        $currentPermissions = $user->permissions()
            ->wherePivot('status', 'active')
            ->pluck('permissions.id')
            ->toArray();

        // Find permissions to deactivate (were active, now not selected)
        $toDeactivate = array_diff($currentPermissions, $permissions);

        // Find permissions to activate (newly selected or reactivate)
        $toActivate = $permissions;

        // Mark unselected permissions as 'delete'
        if (!empty($toDeactivate)) {
            foreach ($toDeactivate as $permissionId) {
                $user->permissions()->updateExistingPivot($permissionId, [
                    'status' => 'delete',
                    'updated_at' => now()
                ]);
            }
        }

        // Add or update selected permissions as 'active'
        foreach ($toActivate as $permissionId) {
            $user->permissions()->syncWithoutDetaching([
                $permissionId => [
                    'status' => 'active',
                    'updated_at' => now()
                ]
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Permissions updated successfully for ' . $user->name);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete yourself.');
        }

        $user->roles()->detach();
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
