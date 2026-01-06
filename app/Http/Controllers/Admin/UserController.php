<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-users')->only(['index', 'show']);
        $this->middleware('permission:create-users')->only(['create', 'store']);
        $this->middleware('permission:edit-users')->only(['edit', 'update']);
        $this->middleware('permission:delete-users')->only('destroy');
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
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Attach roles
        $user->roles()->attach($request->roles);

        // Auto-assign permissions from roles
        $this->autoAssignPermissionsFromRoles($user);

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
        $user->updated_by = Auth::id();

        $user->save();

        // Sync roles
        $user->roles()->sync($request->roles);

        // Auto-assign permissions from updated roles
        $this->autoAssignPermissionsFromRoles($user);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
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
        $user->permissions()->detach();
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show the user permission assignment form.
     */
    public function permissions(User $user)
    {
        // Get all permissions grouped by module
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        // Get user's role permissions
        $rolePermissions = collect();
        foreach ($user->roles as $role) {
            $rolePermissions = $rolePermissions->merge($role->permissions);
        }
        $rolePermissions = $rolePermissions->unique('id')->pluck('id')->toArray();

        // Get user's direct permissions
        $userPermissions = $user->permissions->pluck('id')->toArray();

        return view('admin.users.user_permission', compact('user', 'permissions', 'rolePermissions', 'userPermissions'));
    }

    /**
     * Update user permissions.
     */
    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Sync direct permissions
        $user->permissions()->sync($request->input('permissions', []));

        return redirect()->route('users.permissions', $user)
            ->with('success', 'User permissions updated successfully.');
    }

    /**
     * Auto-assign permissions to user based on their roles.
     * This adds role permissions as direct permissions to the user.
     */
    private function autoAssignPermissionsFromRoles(User $user)
    {
        $rolePermissions = collect();
        foreach ($user->roles as $role) {
            $rolePermissions = $rolePermissions->merge($role->permissions->pluck('id'));
        }

        // Sync permissions without detaching existing ones
        $user->permissions()->syncWithoutDetaching($rolePermissions->unique()->toArray());
    }
}