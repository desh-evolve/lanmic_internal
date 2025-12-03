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

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ url('/home') }}" class="nav-link {{ request()->is('home') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- REQUISITIONS Dropdown -->
                <li class="nav-item has-treeview {{ request()->is('requisitions*') && !request()->is('admin/requisitions*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('requisitions*') && !request()->is('admin/requisitions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>
                            Requisitions
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('requisitions.index') }}" class="nav-link {{ request()->is('requisitions/index') || request()->is('requisitions/*') && !request()->is('requisitions/create*') && !request()->is('admin/requisitions*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Requisitions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('requisitions.create') }}" class="nav-link {{ request()->is('requisitions/create*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Create Requisition</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- RETURNS Dropdown -->
                <li class="nav-item has-treeview {{ request()->is('returns*') && !request()->is('admin/returns*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('returns*') && !request()->is('admin/returns*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-undo-alt"></i>
                        <p>
                            Returns
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('returns.index') }}" class="nav-link {{ request()->is('returns') && !request()->is('returns/create*') && !request()->is('admin/returns*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>My Returns</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('returns.create') }}" class="nav-link {{ request()->is('returns/create*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Create Return</p>
                            </a>
                        </li>
                    </ul>
                </li>

                @if(Auth::user()->hasRole('admin'))

                <li class="nav-header">ADMINISTRATION</li>

                <!-- Requisition Approvals -->
                <li class="nav-item">
                    <a href="{{ route('admin.requisitions.index') }}" class="nav-link {{ request()->is('admin/requisitions*') ? 'active' : '' }}">
                        <i class="fas fa-file-signature nav-icon"></i>
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

                <!-- Return Approvals -->
                <li class="nav-item">
                    <a href="{{ route('admin.returns.index') }}" class="nav-link {{ request()->is('admin/returns*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt nav-icon"></i>
                        <p>
                            Return Approvals
                            @php
                                $pendingReturns = \App\Models\ReturnModel::pending()->count();
                            @endphp
                            @if($pendingReturns > 0)
                                <span class="badge badge-warning right">{{ $pendingReturns }}</span>
                            @endif
                        </p>
                    </a>
                </li>

                <!-- PURCHASE ORDERS Dropdown -->
                <li class="nav-item {{ request()->is('admin/purchase-orders*') ? 'menu-open' : '' }}">
                    <a href="{{ route('admin.purchase-orders.index', ['status' => 'pending']) }}" class="nav-link {{ request()->is('admin/purchase-orders*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>
                            Purchase Orders
                            @php
                                $pendingPOCount = \App\Models\PurchaseOrderItem::where('status', 'pending')->count();
                            @endphp
                            @if($pendingPOCount > 0)
                                <span class="badge badge-warning right">{{ $pendingPOCount }}</span>
                            @endif
                        </p>
                    </a>
                </li>

                <!-- REPORTS Dropdown -->
                <li class="nav-item has-treeview {{ request()->is('admin/reports*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Reports
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reports Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.requisition-summary') }}" class="nav-link {{ request()->routeIs('reports.requisition-summary') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Requisition Summary</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.item-requisition') }}" class="nav-link {{ request()->routeIs('reports.item-requisition') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Item Requisition</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.issued-items') }}" class="nav-link {{ request()->routeIs('reports.issued-items') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Issued Items</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.purchase-order') }}" class="nav-link {{ request()->routeIs('reports.purchase-order') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Purchase Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.returns-summary') }}" class="nav-link {{ request()->routeIs('reports.returns-summary') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Returns Summary</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.grn') }}" class="nav-link {{ request()->routeIs('reports.grn') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>GRN Report</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.scrap') }}" class="nav-link {{ request()->routeIs('reports.scrap') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Scrap Report</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.department-activity') }}" class="nav-link {{ request()->routeIs('reports.department-activity') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Department Activity</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.user-activity') }}" class="nav-link {{ request()->routeIs('reports.user-activity') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>User Activity</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.monthly-summary') }}" class="nav-link {{ request()->routeIs('reports.monthly-summary') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Monthly Summary</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-header">MASTER FILES</li>

                <!-- AUTHENTICATION Dropdown -->
                <li class="nav-item has-treeview {{ request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Authentication
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Users</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->is('admin/roles*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Roles</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->is('admin/permissions*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Permissions</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- ORGANIZATION Dropdown -->
                <li class="nav-item has-treeview {{ request()->is('admin/departments*') || request()->is('admin/sub-departments*') || request()->is('admin/divisions*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/departments*') || request()->is('admin/sub-departments*') || request()->is('admin/divisions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sitemap"></i>
                        <p>
                            Organization
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('departments.index') }}" class="nav-link {{ request()->is('admin/departments*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Departments</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('sub-departments.index') }}" class="nav-link {{ request()->is('admin/sub-departments*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Sub-Departments</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('divisions.index') }}" class="nav-link {{ request()->is('admin/divisions*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Divisions</p>
                            </a>
                        </li>
                    </ul>
                </li>

                @endif
            </ul>
        </nav>
    </div>
</aside>