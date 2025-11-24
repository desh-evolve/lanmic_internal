<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ url('/home') }}" class="brand-link">
        <span class="brand-text font-weight-light">LANMIC Internal</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-white"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ url('/home') }}" class="nav-link {{ request()->is('home') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">REQUISITIONS</li>

                <li class="nav-item">
                    <a href="{{ route('requisitions.index') }}" class="nav-link {{ request()->is('requisitions*') && !request()->is('admin/requisitions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>My Requisitions</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('requisitions.create') }}" class="nav-link">
                        <i class="nav-icon fas fa-plus-circle"></i>
                        <p>Create Requisition</p>
                    </a>
                </li>
                
                @if(Auth::user()->hasRole('admin'))
                <li class="nav-header">ADMINISTRATION</li>
                
                <li class="nav-item">
                    <a href="{{ route('admin.requisitions.index') }}" class="nav-link {{ request()->is('admin/requisitions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clipboard-check"></i>
                        <p>
                            Requisition Approvals
                            @php
                                $pendingCount = \App\Models\Requisition::pending()->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="badge badge-warning right">{{ $pendingCount }}</span>
                            @endif
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>User Management</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->is('admin/roles*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-tag"></i>
                        <p>Role Management</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->is('admin/permissions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-lock"></i>
                        <p>Permission Management</p>
                    </a>
                </li>

                <li class="nav-header">ORGANIZATION</li>

                <li class="nav-item">
                    <a href="{{ route('departments.index') }}" class="nav-link {{ request()->is('admin/departments*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-building"></i>
                        <p>Departments</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('sub-departments.index') }}" class="nav-link {{ request()->is('admin/sub-departments*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sitemap"></i>
                        <p>Sub-Departments</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('divisions.index') }}" class="nav-link {{ request()->is('admin/divisions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p>Divisions</p>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>