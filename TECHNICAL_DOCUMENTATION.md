# LANMIC INTERNAL REQUISITION MANAGEMENT SYSTEM
## Technical Documentation

---

## Table of Contents
1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Database Schema](#database-schema)
4. [Models and Relationships](#models-and-relationships)
5. [Controllers and Business Logic](#controllers-and-business-logic)
6. [SAGE 300 Integration](#sage-300-integration)
7. [Permission System](#permission-system)
8. [Complete Workflows](#complete-workflows)
9. [API Endpoints](#api-endpoints)
10. [Deployment Guide](#deployment-guide)

---

## 1. Project Overview

### Purpose
A Laravel-based web application for managing internal item requisitions integrated with SAGE 300 ERP system. The system handles the complete workflow from requisition creation through approval, item issuing, returns processing, purchase orders, and comprehensive reporting.

### Key Features
- User-wise and role-wise authentication with granular permissions
- Item requisition management with SAGE 300 stock validation
- Automatic purchase order generation for out-of-stock items
- Admin approval workflow for requisitions and returns
- Item issuing with real-time SAGE 300 integration
- Return management with GRN (Goods Return Note) and scrap handling
- Comprehensive reporting with Excel export
- Department/Sub-department/Division organizational structure

### Technology Stack
- **Framework**: Laravel 11
- **PHP**: 8.2+
- **Database**: SQL Server (configurable)
- **Frontend**: Laravel UI with Bootstrap
- **Authentication**: Laravel built-in
- **Excel Export**: maatwebsite/excel
- **ERP Integration**: SAGE 300 Web API (REST)
- **HTTP Client**: Laravel HTTP (Guzzle)

---

## 2. System Architecture

### Application Structure
```
┌─────────────────────────────────────────────────────────┐
│                    Web Interface                         │
│  (User Portal)           │          (Admin Panel)        │
│  - Requisitions          │          - Approvals          │
│  - Returns               │          - Issuing            │
│                          │          - Reports            │
└──────────────┬───────────┴───────────┬──────────────────┘
               │                       │
               ▼                       ▼
    ┌──────────────────────────────────────────┐
    │         Laravel Application               │
    │  ┌────────────────────────────────────┐  │
    │  │  Controllers & Services            │  │
    │  └────────────────────────────────────┘  │
    │  ┌────────────────────────────────────┐  │
    │  │  Permission Middleware             │  │
    │  └────────────────────────────────────┘  │
    │  ┌────────────────────────────────────┐  │
    │  │  Eloquent Models                   │  │
    │  └────────────────────────────────────┘  │
    └──────────┬───────────────┬────────────────┘
               │               │
               ▼               ▼
    ┌──────────────┐   ┌──────────────────┐
    │  SQL Server  │   │   SAGE 300 ERP   │
    │   Database   │   │    (Web API)     │
    └──────────────┘   └──────────────────┘
```

### Directory Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── RequisitionController.php
│   │   ├── ReturnController.php
│   │   ├── HomeController.php
│   │   ├── Sage300Controller.php
│   │   └── Admin/
│   │       ├── RequisitionApprovalController.php
│   │       ├── ReturnApprovalController.php
│   │       ├── PurchaseOrderController.php
│   │       ├── ReportController.php
│   │       ├── UserController.php
│   │       ├── RoleController.php
│   │       ├── PermissionController.php
│   │       ├── DepartmentController.php
│   │       ├── SubDepartmentController.php
│   │       └── DivisionController.php
│   └── Middleware/
│       ├── CheckRole.php
│       └── CheckPermission.php
├── Models/
│   ├── User.php
│   ├── Role.php
│   ├── Permission.php
│   ├── Department.php
│   ├── SubDepartment.php
│   ├── Division.php
│   ├── Requisition.php
│   ├── RequisitionItem.php
│   ├── RequisitionIssuedItem.php
│   ├── PurchaseOrderItem.php
│   ├── ReturnModel.php
│   ├── ReturnItem.php
│   ├── GrnItem.php
│   └── ScrapItem.php
├── Services/
│   ├── Sage300Service.php
│   └── ItemAvailabilityService.php
└── Exports/
    ├── RequisitionSummaryExport.php
    ├── ItemRequisitionExport.php
    ├── IssuedItemsExport.php
    ├── PurchaseOrderExport.php
    ├── ReturnsSummaryExport.php
    ├── GrnExport.php
    ├── ScrapExport.php
    ├── DepartmentActivityExport.php
    ├── UserActivityExport.php
    └── MonthlySummaryExport.php
```

---

## 3. Database Schema

### Authentication & Authorization Tables

#### users
```sql
id (PK)
name
email (unique)
password
status (default: 'active')
created_at, updated_at
created_by, updated_by
```

#### roles
```sql
id (PK)
name (unique)
description
status
created_at, updated_at
created_by, updated_by
```

#### permissions
```sql
id (PK)
name (unique)
module (dashboard, users, requisitions, etc.)
description
status
created_at, updated_at
created_by, updated_by
```

#### Pivot Tables
```sql
role_user (user_id, role_id)
permission_role (permission_id, role_id)
permission_user (user_id, permission_id) -- Direct permissions
```

### Organizational Structure Tables

#### departments
```sql
id (PK)
name
short_code (unique)
description
status
created_at, updated_at
created_by, updated_by
```

#### sub_departments
```sql
id (PK)
name
short_code (unique)
description
status
created_at, updated_at
created_by, updated_by
```

#### divisions
```sql
id (PK)
name
short_code (unique)
description
status
created_at, updated_at
created_by, updated_by
```

#### Pivot Tables
```sql
department_sub_department (department_id, sub_department_id)
division_sub_department (division_id, sub_department_id)
```

**Relationships**:
- Department ↔ Sub-Department (Many-to-Many)
- Sub-Department ↔ Division (Many-to-Many)

### Requisition System Tables

#### requisitions
```sql
id (PK)
requisition_number (unique) -- Format: REQ-YYYYMM-0001
user_id (FK → users)
department_id (FK → departments)
sub_department_id (FK → sub_departments)
division_id (FK → divisions)
approve_status (pending|approved|rejected)
approved_by (FK → users, nullable)
approved_at (nullable)
clear_status (pending|cleared)
cleared_by (FK → users, nullable)
cleared_at (nullable)
rejection_reason (text, nullable)
notes (text, nullable)
status (active|delete) -- Soft delete
created_at, updated_at
created_by, updated_by
```

#### requisition_items
```sql
id (PK)
requisition_id (FK → requisitions)
location_code (from SAGE 300)
item_code (from SAGE 300)
item_name
item_category
unit
quantity
specifications (nullable)
status (active|delete|rejected)
created_at, updated_at
created_by, updated_by
```
**Note**: unit_price and total_price removed (prices from SAGE during issuing)

#### requisition_issued_items
```sql
id (PK)
requisition_id (FK → requisitions)
requisition_item_id (FK → requisition_items)
location_code (SAGE location issued from)
item_code
item_name
item_category
unit
issued_quantity
unit_price (from SAGE at issue time)
total_price (calculated)
reference_number_1 (SAGE transaction ref)
reference_number_2 (SAGE transaction ref)
notes (nullable)
issued_by (FK → users)
issued_at
status (active|delete)
created_at, updated_at
created_by, updated_by
```

#### purchase_order_items
```sql
id (PK)
requisition_id (FK → requisitions)
location_code
item_code
item_name
item_category
unit
quantity
status (pending|cleared)
cleared_by (FK → users, nullable)
cleared_at (nullable)
created_at, updated_at
created_by, updated_by
```
**Purpose**: Items that need to be purchased (not in stock)

### Returns System Tables

#### returns
```sql
id (PK)
requisition_id (FK → requisitions, nullable)
returned_by (FK → users)
returned_at
status (pending|cleared|delete)
created_at, updated_at
created_by, updated_by
```

#### return_items
```sql
id (PK)
return_id (FK → returns)
requisition_issued_item_id (FK → requisition_issued_items)
return_type ('used' | 'same')
location_code (return location)
item_code
item_name
item_category
unit
quantity (return quantity)
approve_status (pending|approved|rejected|partial)
approved_by (FK → users, nullable)
approved_at (nullable)
notes (user notes, nullable)
admin_note (admin notes, nullable)
status (active|delete)
created_at, updated_at
created_by, updated_by
```

#### grn_items (Goods Return Note)
```sql
id (PK)
return_id (FK → returns)
return_item_id (FK → return_items)
item_code
item_name
item_category
unit
location_code (GRN location)
unit_price (from SAGE)
total_price (calculated)
grn_quantity (quantity returned to stock)
reference_number_1 (SAGE transaction ref)
reference_number_2 (SAGE transaction ref)
processed_by (FK → users)
processed_at
status (active|delete)
created_at, updated_at
created_by, updated_by
```

#### scrap_items
```sql
id (PK)
return_id (FK → returns)
return_item_id (FK → return_items)
item_code
item_name
item_category
unit
location_code (original location)
unit_price (from return context)
total_price (calculated)
scrap_quantity (quantity scrapped)
processed_by (FK → users)
processed_at
status (active|delete)
created_at, updated_at
created_by, updated_by
```

---

## 4. Models and Relationships

### User Model

**Location**: `app/Models/User.php`

**Relationships**:
```php
// Many-to-Many with Role
public function roles()
{
    return $this->belongsToMany(Role::class, 'role_user');
}

// Many-to-Many with Permission (direct permissions)
public function permissions()
{
    return $this->belongsToMany(Permission::class, 'permission_user');
}

// One-to-Many with Requisition
public function requisitions()
{
    return $this->hasMany(Requisition::class);
}
```

**Key Methods**:
```php
// Check if user has specific role
public function hasRole($role): bool

// Check if user has any of given roles
public function hasAnyRole(array $roles): bool

// Get all permissions (role-based + direct)
public function getAllPermissions(): Collection

// Check if user has specific permission
public function hasPermission($permission): bool

// Check if user has any of given permissions
public function hasAnyPermission(array $permissions): bool
```

### Requisition Model

**Location**: `app/Models/Requisition.php`

**Relationships**:
```php
public function user()              // belongsTo(User) - creator
public function department()        // belongsTo(Department)
public function subDepartment()     // belongsTo(SubDepartment)
public function division()          // belongsTo(Division)
public function approvedBy()        // belongsTo(User)
public function clearedBy()         // belongsTo(User)
public function items()             // hasMany(RequisitionItem)
public function issuedItems()       // hasMany(RequisitionIssuedItem)
public function purchaseOrderItems()// hasMany(PurchaseOrderItem)
public function returns()           // hasMany(ReturnModel)
```

**Scopes**:
```php
public function scopePending($query)   // approve_status='pending'
public function scopeApproved($query)  // approve_status='approved'
public function scopeRejected($query)  // approve_status='rejected'
public function scopeActive($query)    // status='active'
```

**Static Methods**:
```php
public static function generateRequisitionNumber(): string
// Returns: REQ-YYYYMM-0001 (auto-incremented)
```

### RequisitionItem Model

**Location**: `app/Models/RequisitionItem.php`

**Relationships**:
```php
public function requisition()    // belongsTo(Requisition)
public function issuedItems()    // hasMany(RequisitionIssuedItem)
```

**Accessors**:
```php
// Get remaining quantity to be issued
public function getRemainingQuantityAttribute(): float
// Returns: requested quantity - total issued

// Check if item fully issued
public function isFullyIssued(): bool
```

### RequisitionIssuedItem Model

**Location**: `app/Models/RequisitionIssuedItem.php`

**Relationships**:
```php
public function requisition()       // belongsTo(Requisition)
public function requisitionItem()   // belongsTo(RequisitionItem)
public function issuedBy()          // belongsTo(User)
public function returnItems()       // hasMany(ReturnItem)
```

### ReturnModel Model

**Location**: `app/Models/ReturnModel.php`

**Relationships**:
```php
public function requisition()   // belongsTo(Requisition)
public function returnedBy()    // belongsTo(User)
public function items()         // hasMany(ReturnItem)
public function grnItems()      // hasMany(GrnItem)
public function scrapItems()    // hasMany(ScrapItem)
```

**Scopes**:
```php
public function scopePending($query)   // status='pending'
public function scopeCleared($query)   // status='cleared'
public function scopeActive($query)    // status!='delete'
```

**Methods**:
```php
public function isPending(): bool
public function allItemsApproved(): bool
```

### ReturnItem Model

**Location**: `app/Models/ReturnItem.php`

**Relationships**:
```php
public function return()                 // belongsTo(ReturnModel)
public function requisitionIssuedItem() // belongsTo(RequisitionIssuedItem)
public function approvedBy()            // belongsTo(User)
public function grnItem()               // hasOne(GrnItem)
public function scrapItem()             // hasOne(ScrapItem)
```

**Methods**:
```php
public function isPending(): bool     // approve_status='pending'
public function isApproved(): bool    // approve_status='approved'
public function isRejected(): bool    // approve_status='rejected'
public function isPartial(): bool     // approve_status='partial'
```

### Department/SubDepartment/Division Models

**Relationships**:
```php
// Department Model
public function subDepartments()  // belongsToMany(SubDepartment)
public function requisitions()    // hasMany(Requisition)

// SubDepartment Model
public function departments()     // belongsToMany(Department)
public function divisions()       // belongsToMany(Division)
public function requisitions()    // hasMany(Requisition)

// Division Model
public function subDepartments()  // belongsToMany(SubDepartment)
public function requisitions()    // hasMany(Requisition)
```

**Common Scope**:
```php
public function scopeActive($query) // status='active'
```

---

## 5. Controllers and Business Logic

### User-Facing Controllers

#### RequisitionController

**Location**: `app/Http/Controllers/RequisitionController.php`
**Route Prefix**: `/requisitions`
**Middleware**: `auth`, `permission:view-requisitions,create-requisitions`

**Key Methods**:

##### index()
```php
// List user's own requisitions
// Filters: approve_status, date range
// Order: latest first
return view('requisitions.index', compact('requisitions'));
```

##### create()
```php
// Show create form
// Loads:
// - Departments
// - SAGE items (via Sage300Service)
// - Locations (storage/locations.json)
return view('requisitions.create', compact('departments', 'items', 'locations'));
```

##### store(Request $request)
```php
// Validation:
// - department_id, sub_department_id, division_id required
// - items array required (item_code, location_code, quantity, specifications)

// Generate requisition number
$requisitionNumber = Requisition::generateRequisitionNumber();

// Create requisition
$requisition = Requisition::create([
    'requisition_number' => $requisitionNumber,
    'user_id' => auth()->id(),
    'department_id' => $request->department_id,
    // ... other fields
    'approve_status' => 'pending',
    'clear_status' => 'pending',
]);

// For each item:
foreach ($request->items as $itemData) {
    // Check availability using ItemAvailabilityService
    $availability = ItemAvailabilityService::splitAvailableAndPO(
        $itemData['item_code'],
        $itemData['location_code'],
        $itemData['quantity'],
        $requisition->id
    );

    // Create requisition item (all items, even if PO)
    $requisition->items()->create([
        'item_code' => $itemData['item_code'],
        'quantity' => $itemData['quantity'],
        // ... other fields
    ]);

    // If quantity exceeds stock, create PO item
    if ($availability['needs_po'] > 0) {
        $requisition->purchaseOrderItems()->create([
            'item_code' => $itemData['item_code'],
            'quantity' => $availability['needs_po'],
            // ... other fields
            'status' => 'pending',
        ]);
    }
}

return redirect()->route('requisitions.index')
    ->with('success', 'Requisition created successfully');
```

##### show($id)
```php
// Show requisition details
// Loads:
// - Requisition with all relationships
// - Issued items grouped by item_code
// - Purchase order items
// Authorization: User can only view own requisitions
return view('requisitions.show', compact('requisition'));
```

##### destroy($id)
```php
// Soft delete requisition
// Authorization:
// - User can only delete own requisitions
// - Only pending requisitions can be deleted

// Update status to 'delete'
$requisition->update(['status' => 'delete']);

// Also soft delete all items
$requisition->items()->update(['status' => 'delete']);
$requisition->purchaseOrderItems()->update(['status' => 'delete']);

return redirect()->route('requisitions.index')
    ->with('success', 'Requisition deleted successfully');
```

##### API Methods:

```php
// Get sub-departments for a department
public function getSubDepartments($departmentId)
{
    $subDepartments = Department::find($departmentId)
        ->subDepartments()
        ->where('status', 'active')
        ->get();
    return response()->json($subDepartments);
}

// Get divisions for a sub-department
public function getDivisions($subDepartmentId)
{
    $divisions = SubDepartment::find($subDepartmentId)
        ->divisions()
        ->where('status', 'active')
        ->get();
    return response()->json($divisions);
}

// Get item availability at a location
public function getItemAvailability($itemCode)
{
    $locationCode = request('location_code');
    $requisitionId = request('requisition_id'); // For excluding current requisition

    $availability = ItemAvailabilityService::getAvailableQuantity(
        $itemCode,
        $locationCode,
        $requisitionId
    );

    return response()->json([
        'available' => $availability,
        'message' => $availability > 0
            ? "Available: $availability"
            : "Out of stock - will create PO"
    ]);
}

// Get all pending approval items (for FIFO display)
public function getPendingApprovalItems()
{
    $pendingItems = ItemAvailabilityService::getAllPendingQuantity();
    return response()->json($pendingItems);
}
```

#### ReturnController

**Location**: `app/Http/Controllers/ReturnController.php`
**Route Prefix**: `/returns`
**Middleware**: `auth`, `permission:view-returns,create-returns`

**Key Methods**:

##### index()
```php
// List user's own returns
// Filters: status, date range
// Order: latest first
return view('returns.index', compact('returns'));
```

##### create()
```php
// Show create form
// Loads:
// - User's approved requisitions with issued items
return view('returns.create', compact('requisitions'));
```

##### store(Request $request)
```php
// Validation:
// - requisition_id optional
// - items array required (issued_item_id, return_type, quantity, location_code, notes)

// Create return
$return = ReturnModel::create([
    'requisition_id' => $request->requisition_id,
    'returned_by' => auth()->id(),
    'returned_at' => now(),
    'status' => 'pending',
]);

// For each item:
foreach ($request->items as $itemData) {
    $issuedItem = RequisitionIssuedItem::find($itemData['issued_item_id']);

    // Validation: Cannot return more than issued
    $alreadyReturned = ReturnItem::where('requisition_issued_item_id', $issuedItem->id)
        ->sum('quantity');
    $maxReturnQty = $issuedItem->issued_quantity - $alreadyReturned;

    if ($itemData['quantity'] > $maxReturnQty) {
        throw new Exception("Cannot return more than issued quantity");
    }

    // Create return item
    $return->items()->create([
        'requisition_issued_item_id' => $issuedItem->id,
        'return_type' => $itemData['return_type'], // 'used' or 'same'
        'quantity' => $itemData['quantity'],
        'location_code' => $itemData['location_code'],
        'item_code' => $issuedItem->item_code,
        'item_name' => $issuedItem->item_name,
        // ... other fields
        'approve_status' => 'pending',
    ]);
}

return redirect()->route('returns.index')
    ->with('success', 'Return created successfully');
```

##### show($id)
```php
// Show return details with items
// Authorization: User can only view own returns
// Loads:
// - Return with items
// - GRN items
// - Scrap items
return view('returns.show', compact('return'));
```

##### destroy($id)
```php
// Soft delete return
// Authorization:
// - User can only delete own returns
// - Only pending returns can be deleted

$return->update(['status' => 'delete']);
$return->items()->update(['status' => 'delete']);

return redirect()->route('returns.index')
    ->with('success', 'Return deleted successfully');
```

##### API Method:

```php
// Get issued items for a requisition (for return creation)
public function getIssuedItems($requisitionId)
{
    $issuedItems = RequisitionIssuedItem::where('requisition_id', $requisitionId)
        ->where('status', 'active')
        ->get()
        ->map(function ($item) {
            // Calculate already returned quantity
            $alreadyReturned = ReturnItem::where('requisition_issued_item_id', $item->id)
                ->sum('quantity');

            $item->already_returned = $alreadyReturned;
            $item->max_return_qty = $item->issued_quantity - $alreadyReturned;
            return $item;
        });

    return response()->json($issuedItems);
}
```

---

### Admin Controllers

#### RequisitionApprovalController

**Location**: `app/Http/Controllers/Admin/RequisitionApprovalController.php`
**Route Prefix**: `/admin/requisitions`
**Middleware**: `auth`, `role:admin`, `permission:approve-requisitions,issue-requisitions`

**Key Methods**:

##### index()
```php
// List all requisitions with filters
// Filters:
// - approve_status (pending, approved, rejected)
// - clear_status (pending, cleared)
// - date range
// - department, sub_department, division
// - user
// Order: latest first
// Statistics: total, pending, approved, rejected counts

return view('admin.requisitions.index', compact('requisitions', 'stats'));
```

##### show($id)
```php
// Show requisition details
// Loads:
// - Requisition with all relationships
// - Items with issued quantities
// - Purchase order items
// - Issue history

return view('admin.requisitions.show', compact('requisition'));
```

##### approve($id)
```php
// Approve requisition
// Validation: Only pending requisitions can be approved

$requisition->update([
    'approve_status' => 'approved',
    'approved_by' => auth()->id(),
    'approved_at' => now(),
]);

return redirect()->route('admin.requisitions.show', $requisition)
    ->with('success', 'Requisition approved successfully');
```

##### reject(Request $request, $id)
```php
// Reject requisition
// Validation:
// - rejection_reason required
// - Only pending requisitions can be rejected

$requisition->update([
    'approve_status' => 'rejected',
    'approved_by' => auth()->id(),
    'approved_at' => now(),
    'rejection_reason' => $request->rejection_reason,
]);

// Mark all items as rejected
$requisition->items()->update(['status' => 'rejected']);

return redirect()->route('admin.requisitions.index')
    ->with('success', 'Requisition rejected');
```

##### issueItemsForm($id)
```php
// Show issue items form
// Validation: Only approved requisitions

// For each requisition item:
foreach ($requisition->items as $item) {
    // Get SAGE locations for this item
    $locations = Sage300Service::getItemLocations($item->item_code);

    // For each location, calculate available quantity
    foreach ($locations as &$location) {
        $pending = ItemAvailabilityService::getPendingQuantity(
            $item->item_code,
            $requisition->id
        );

        $location['available'] = $location['quantity_on_hand'] - $pending;
    }

    $item->locations = $locations;
    $item->remaining_qty = $item->getRemainingQuantityAttribute();
}

return view('admin.requisitions.issue-items', compact('requisition'));
```

##### issueItems(Request $request, $id)
```php
// Process item issuing
// Validation:
// - items array required (item_id, location_code, quantity, notes)
// - quantity must not exceed available stock
// - quantity must not exceed remaining to issue

DB::beginTransaction();
try {
    foreach ($request->items as $itemData) {
        $requisitionItem = RequisitionItem::find($itemData['item_id']);

        // Validate quantity against available stock
        $available = ItemAvailabilityService::getAvailableQuantity(
            $requisitionItem->item_code,
            $itemData['location_code'],
            $requisition->id
        );

        if ($itemData['quantity'] > $available) {
            throw new Exception("Insufficient stock at location");
        }

        // Post to SAGE 300
        $sageResponse = Sage300Service::postAdjustment([
            'TransactionType' => 'BothDecrease',
            'ItemNumber' => $requisitionItem->item_code,
            'Location' => $itemData['location_code'],
            'Quantity' => $itemData['quantity'],
            'Comment' => "Requisition: {$requisition->requisition_number}",
        ]);

        // Extract data from SAGE response
        $unitPrice = $sageResponse['UnitCost'];
        $referenceNumber1 = $sageResponse['SequenceNumber'];
        $referenceNumber2 = $sageResponse['BatchNumber'];

        // Create issued item record
        RequisitionIssuedItem::create([
            'requisition_id' => $requisition->id,
            'requisition_item_id' => $requisitionItem->id,
            'location_code' => $itemData['location_code'],
            'item_code' => $requisitionItem->item_code,
            'item_name' => $requisitionItem->item_name,
            'item_category' => $requisitionItem->item_category,
            'unit' => $requisitionItem->unit,
            'issued_quantity' => $itemData['quantity'],
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $itemData['quantity'],
            'reference_number_1' => $referenceNumber1,
            'reference_number_2' => $referenceNumber2,
            'notes' => $itemData['notes'] ?? null,
            'issued_by' => auth()->id(),
            'issued_at' => now(),
        ]);
    }

    // Check if all items fully issued
    $allItemsIssued = true;
    foreach ($requisition->items as $item) {
        if (!$item->isFullyIssued()) {
            $allItemsIssued = false;
            break;
        }
    }

    // Auto-clear requisition if all items issued
    if ($allItemsIssued) {
        $requisition->update([
            'clear_status' => 'cleared',
            'cleared_by' => auth()->id(),
            'cleared_at' => now(),
        ]);
    }

    DB::commit();

    return redirect()->route('admin.requisitions.show', $requisition)
        ->with('success', 'Items issued successfully');

} catch (\Exception $e) {
    DB::rollBack();
    return back()->with('error', 'Issue failed: ' . $e->getMessage());
}
```

#### ReturnApprovalController

**Location**: `app/Http/Controllers/Admin/ReturnApprovalController.php`
**Route Prefix**: `/admin/returns`
**Middleware**: `auth`, `role:admin`, `permission:approve-returns`

**Key Methods**:

##### index()
```php
// List all returns with filters
// Filters:
// - status (pending, cleared)
// - date range
// - user
// Statistics: total, pending, cleared counts

return view('admin.returns.index', compact('returns', 'stats'));
```

##### show($id)
```php
// Show return details
// Loads:
// - Return with items
// - GRN items
// - Scrap items
// - Original requisition

return view('admin.returns.show', compact('return'));
```

##### approveItemsForm($id)
```php
// Show approval form
// Loads:
// - Return with pending items
// - SAGE locations
// - Item prices from SAGE

foreach ($return->items as $item) {
    // Get price from SAGE
    $sageItem = Sage300Service::getItem($item->item_code);
    $item->unit_price = $sageItem['StandardUnitCost'];
}

return view('admin.returns.approve-items', compact('return'));
```

##### approveItems(Request $request, $id)
```php
// Process return approval
// Validation:
// - items array required (item_id, grn_qty, scrap_qty, location_code, unit_price, admin_note)
// - grn_qty + scrap_qty must equal return_item.quantity
// - location_code required
// - unit_price required

DB::beginTransaction();
try {
    foreach ($request->items as $itemData) {
        $returnItem = ReturnItem::find($itemData['item_id']);

        // Validate quantities
        $totalQty = $itemData['grn_qty'] + $itemData['scrap_qty'];
        if ($totalQty != $returnItem->quantity) {
            throw new Exception("GRN + Scrap quantity must equal return quantity");
        }

        // Process GRN (if any)
        if ($itemData['grn_qty'] > 0) {
            // Post to SAGE 300
            $sageResponse = Sage300Service::postGrnAdjustment([
                'TransactionType' => 'BothIncrease',
                'ItemNumber' => $itemData['item_code'] ?? $returnItem->item_code,
                'Location' => $itemData['location_code'],
                'Quantity' => $itemData['grn_qty'],
                'UnitCost' => $itemData['unit_price'],
                'Comment' => "Return GRN: {$return->id}",
            ]);

            // Create GRN item record
            GrnItem::create([
                'return_id' => $return->id,
                'return_item_id' => $returnItem->id,
                'item_code' => $itemData['item_code'] ?? $returnItem->item_code,
                'item_name' => $returnItem->item_name,
                'item_category' => $returnItem->item_category,
                'unit' => $returnItem->unit,
                'location_code' => $itemData['location_code'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['unit_price'] * $itemData['grn_qty'],
                'grn_quantity' => $itemData['grn_qty'],
                'reference_number_1' => $sageResponse['SequenceNumber'],
                'reference_number_2' => $sageResponse['BatchNumber'],
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
        }

        // Process Scrap (if any)
        if ($itemData['scrap_qty'] > 0) {
            // No SAGE posting for scrap items
            ScrapItem::create([
                'return_id' => $return->id,
                'return_item_id' => $returnItem->id,
                'item_code' => $returnItem->item_code,
                'item_name' => $returnItem->item_name,
                'item_category' => $returnItem->item_category,
                'unit' => $returnItem->unit,
                'location_code' => $returnItem->location_code,
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['unit_price'] * $itemData['scrap_qty'],
                'scrap_quantity' => $itemData['scrap_qty'],
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
        }

        // Update return item status
        $approveStatus = 'approved'; // Default
        if ($itemData['grn_qty'] > 0 && $itemData['scrap_qty'] > 0) {
            $approveStatus = 'partial'; // Both GRN and scrap
        } elseif ($itemData['scrap_qty'] == $returnItem->quantity) {
            $approveStatus = 'rejected'; // All scrapped
        }

        $returnItem->update([
            'approve_status' => $approveStatus,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_note' => $itemData['admin_note'] ?? null,
        ]);
    }

    // Check if all items processed
    if ($return->allItemsApproved()) {
        $return->update(['status' => 'cleared']);
    }

    DB::commit();

    return redirect()->route('admin.returns.show', $return)
        ->with('success', 'Return items processed successfully');

} catch (\Exception $e) {
    DB::rollBack();
    return back()->with('error', 'Approval failed: ' . $e->getMessage());
}
```

#### PurchaseOrderController

**Location**: `app/Http/Controllers/Admin/PurchaseOrderController.php`
**Route Prefix**: `/admin/purchase-orders`
**Middleware**: `auth`, `role:admin`, `permission:view-purchase-orders,clear-purchase-orders`

**Key Methods**:

##### index()
```php
// List PO items
// Filters:
// - status (pending, cleared)
// - date range
// - item_code
// - group_by (requisition | item)

if ($request->group_by == 'item') {
    // Group by item code
    $poItems = PurchaseOrderItem::select('item_code', 'item_name')
        ->selectRaw('SUM(quantity) as total_quantity')
        ->selectRaw('COUNT(DISTINCT requisition_id) as requisition_count')
        ->where('status', $request->status ?? 'pending')
        ->groupBy('item_code', 'item_name')
        ->get();
} else {
    // Group by requisition (default)
    $poItems = PurchaseOrderItem::with('requisition')
        ->where('status', $request->status ?? 'pending')
        ->get()
        ->groupBy('requisition_id');
}

return view('admin.purchase-orders.index', compact('poItems'));
```

##### show($id)
```php
// Show PO item details
$poItem = PurchaseOrderItem::with(['requisition', 'requisition.user'])->findOrFail($id);
return view('admin.purchase-orders.show', compact('poItem'));
```

##### clear(Request $request)
```php
// Clear selected PO items
// Validation: po_item_ids array required

PurchaseOrderItem::whereIn('id', $request->po_item_ids)
    ->update([
        'status' => 'cleared',
        'cleared_by' => auth()->id(),
        'cleared_at' => now(),
    ]);

return redirect()->route('admin.purchase-orders.index')
    ->with('success', 'Purchase orders cleared successfully');
```

##### bulkClear(Request $request)
```php
// Bulk clear by requisition or item
// Options:
// - clear_type: 'requisition' | 'item' | 'all'
// - requisition_id: if clear_type='requisition'
// - item_code: if clear_type='item'

$query = PurchaseOrderItem::where('status', 'pending');

if ($request->clear_type == 'requisition') {
    $query->where('requisition_id', $request->requisition_id);
} elseif ($request->clear_type == 'item') {
    $query->where('item_code', $request->item_code);
}
// else clear all pending

$query->update([
    'status' => 'cleared',
    'cleared_by' => auth()->id(),
    'cleared_at' => now(),
]);

return redirect()->route('admin.purchase-orders.index')
    ->with('success', 'Purchase orders cleared successfully');
```

---

## 6. SAGE 300 Integration

### Sage300Service Class

**Location**: `app/Services/Sage300Service.php`

This service handles all communication with SAGE 300 Web API.

**Configuration**:
```php
// config/sage300.php
return [
    'base_url' => env('SAGE300_BASE_URL', 'http://192.168.11.60/Sage300WebApi/v1.0/-/SAMINC'),
    'username' => env('SAGE300_USERNAME', 'WEBUSER'),
    'password' => env('SAGE300_PASSWORD', 'Webuser'),
    'timeout' => env('SAGE300_TIMEOUT', 30),
];
```

**Constructor**:
```php
public function __construct()
{
    $this->baseUrl = config('sage300.base_url');
    $this->username = config('sage300.username');
    $this->password = config('sage300.password');
    $this->timeout = config('sage300.timeout');
}
```

**HTTP Request Methods**:

```php
// Generic GET request
protected function get($endpoint, $params = [])
{
    $url = $this->baseUrl . '/' . $endpoint;

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    $response = Http::timeout($this->timeout)
        ->withBasicAuth($this->username, $this->password)
        ->get($url);

    if ($response->failed()) {
        throw new \Exception('SAGE API Error: ' . $response->body());
    }

    return $response->json();
}

// Generic POST request
protected function post($endpoint, $data = [])
{
    $url = $this->baseUrl . '/' . $endpoint;

    $response = Http::timeout($this->timeout)
        ->withBasicAuth($this->username, $this->password)
        ->withHeaders(['Content-Type' => 'application/json'])
        ->post($url, $data);

    if ($response->failed()) {
        throw new \Exception('SAGE API Error: ' . $response->body());
    }

    return $response->json();
}
```

**Item Methods**:

```php
// Get all items
public function getItems()
{
    return $this->get('IC/ICItems');
}

// Get single item by code
public function getItem($itemCode)
{
    return $this->get("IC/ICItems('{$itemCode}')");
}

// Get item pricing
public function getItemPricing($itemCode)
{
    return $this->get('IC/ICItemPricing', [
        '$filter' => "UnformattedItemNumber eq '{$itemCode}'"
    ]);
}
```

**Location Methods**:

```php
// Get all locations
public function getLocations()
{
    return $this->get('IC/ICLocations');
}

// Get single location
public function getLocation($locationCode)
{
    return $this->get('IC/ICLocations', [
        '$filter' => "LocationKey eq '{$locationCode}'"
    ]);
}

// Get all locations for an item with quantities
public function getItemLocations($itemCode)
{
    $response = $this->get('IC/ICLocationDetails', [
        '$filter' => "ItemNumber eq '{$itemCode}'"
    ]);

    return collect($response)->map(function ($location) {
        return [
            'location_code' => $location['Location'],
            'quantity_on_hand' => $location['QuantityOnHand'],
            'quantity_available' => $location['QuantityAvailable'],
            'unit_cost' => $location['UnitCost'],
        ];
    })->toArray();
}

// Get item quantity at specific location
public function getItemLocationQuantity($itemCode, $locationCode)
{
    $response = $this->get(
        "IC/ICLocationDetails(ItemNumber='{$itemCode}',Location='{$locationCode}')"
    );

    return [
        'quantity_on_hand' => $response['QuantityOnHand'],
        'quantity_available' => $response['QuantityAvailable'],
        'unit_cost' => $response['UnitCost'],
    ];
}
```

**Inventory Adjustment Methods**:

```php
// Post inventory adjustment (for item issuing)
public function postAdjustment($data)
{
    /*
     * Expected $data structure:
     * [
     *     'TransactionType' => 'BothDecrease',
     *     'ItemNumber' => 'ITEM001',
     *     'Location' => 'MAIN',
     *     'Quantity' => 10,
     *     'Comment' => 'Requisition: REQ-202401-0001',
     * ]
     *
     * Returns:
     * [
     *     'SequenceNumber' => 123456,      // reference_number_1
     *     'BatchNumber' => 'ICA000001',    // reference_number_2
     *     'UnitCost' => 25.50,            // unit_price
     *     // ... other fields
     * ]
     */

    $adjustmentData = [
        'TransactionType' => $data['TransactionType'], // BothDecrease
        'Details' => [
            [
                'ItemNumber' => $data['ItemNumber'],
                'Location' => $data['Location'],
                'Quantity' => $data['Quantity'],
            ]
        ],
        'Comment' => $data['Comment'] ?? '',
    ];

    return $this->post('IC/ICAdjustments', $adjustmentData);
}

// Post GRN adjustment (for returns to stock)
public function postGrnAdjustment($data)
{
    /*
     * Expected $data structure:
     * [
     *     'TransactionType' => 'BothIncrease',
     *     'ItemNumber' => 'ITEM001',
     *     'Location' => 'MAIN',
     *     'Quantity' => 5,
     *     'UnitCost' => 25.50,
     *     'Comment' => 'Return GRN: 123',
     * ]
     *
     * Returns:
     * [
     *     'SequenceNumber' => 123457,      // reference_number_1
     *     'BatchNumber' => 'ICA000002',    // reference_number_2
     *     'UnitCost' => 25.50,            // unit_price
     *     // ... other fields
     * ]
     */

    $adjustmentData = [
        'TransactionType' => $data['TransactionType'], // BothIncrease
        'Details' => [
            [
                'ItemNumber' => $data['ItemNumber'],
                'Location' => $data['Location'],
                'Quantity' => $data['Quantity'],
                'UnitCost' => $data['UnitCost'],
            ]
        ],
        'Comment' => $data['Comment'] ?? '',
    ];

    return $this->post('IC/ICAdjustments', $adjustmentData);
}
```

### Transaction Types

**BothDecrease** (Used for Item Issuing):
- Decreases Quantity on Hand
- Decreases Total Cost Value
- Posted when admin issues items to users
- Does NOT require UnitCost input (uses SAGE's current cost)
- Returns: UnitCost, SequenceNumber, BatchNumber

**BothIncrease** (Used for GRN Processing):
- Increases Quantity on Hand
- Increases Total Cost Value
- Posted when admin processes good returns
- REQUIRES UnitCost input (admin provides)
- Returns: UnitCost, SequenceNumber, BatchNumber

### ItemAvailabilityService Class

**Location**: `app/Services/ItemAvailabilityService.php`

This service calculates real-time item availability considering pending requisitions.

**Purpose**: Ensure accurate stock availability by considering:
1. SAGE 300 stock quantity
2. Pending (not yet issued) requisition items
3. Already issued quantities

**Key Methods**:

```php
// Get available quantity for an item at a location
public static function getAvailableQuantity($itemCode, $locationCode, $excludeRequisitionId = null)
{
    // Get stock from SAGE
    $sageQty = Sage300Service::getItemLocationQuantity($itemCode, $locationCode);
    $stockQty = $sageQty['quantity_on_hand'];

    // Get pending quantity (requested but not issued)
    $pendingQty = static::getPendingQuantity($itemCode, $excludeRequisitionId);

    // Available = Stock - Pending
    $available = $stockQty - $pendingQty;

    return max(0, $available); // Cannot be negative
}

// Get total pending quantity for an item across all locations
public static function getPendingQuantity($itemCode, $excludeRequisitionId = null)
{
    // Get all pending requisitions
    $query = Requisition::where('approve_status', 'approved')
        ->where('clear_status', 'pending');

    if ($excludeRequisitionId) {
        $query->where('id', '!=', $excludeRequisitionId);
    }

    $pendingRequisitions = $query->pluck('id');

    // Sum requested quantities
    $requestedQty = RequisitionItem::whereIn('requisition_id', $pendingRequisitions)
        ->where('item_code', $itemCode)
        ->where('status', 'active')
        ->sum('quantity');

    // Subtract already issued quantities
    $issuedQty = RequisitionIssuedItem::whereIn('requisition_id', $pendingRequisitions)
        ->where('item_code', $itemCode)
        ->where('status', 'active')
        ->sum('issued_quantity');

    return $requestedQty - $issuedQty;
}

// Get all pending items grouped by item code
public static function getAllPendingQuantity($excludeRequisitionId = null)
{
    // Similar logic as above but returns array grouped by item_code
    // Used for displaying pending items to users (FIFO awareness)

    return [
        'ITEM001' => 50,
        'ITEM002' => 25,
        // ...
    ];
}

// Split requested quantity into available and needs PO
public static function splitAvailableAndPO($itemCode, $locationCode, $requestedQty, $excludeRequisitionId = null)
{
    $available = static::getAvailableQuantity($itemCode, $locationCode, $excludeRequisitionId);

    if ($requestedQty <= $available) {
        // Fully available
        return [
            'available' => $requestedQty,
            'needs_po' => 0,
        ];
    } else {
        // Partial/No stock
        return [
            'available' => $available,
            'needs_po' => $requestedQty - $available,
        ];
    }
}
```

---

## 7. Permission System

### Permission Modules

The application has 11 permission modules:

1. **dashboard** - Dashboard access
2. **users** - User management (CRUD + assign permissions)
3. **roles** - Role management (CRUD)
4. **permissions** - Permission management (CRUD)
5. **departments** - Department management (CRUD)
6. **sub-departments** - Sub-department management (CRUD)
7. **divisions** - Division management (CRUD)
8. **requisitions** - Requisition management (view, create, edit, delete, approve, issue)
9. **purchase-orders** - Purchase order management (view, clear)
10. **returns** - Return management (view, create, edit, delete, approve)
11. **reports** - Report access (view, export)

### Permission Structure

Each module has standard CRUD permissions plus custom ones:

**Standard**:
- `view-{module}`
- `create-{module}`
- `edit-{module}`
- `delete-{module}`

**Custom**:
- `assign-user-permissions` (users module)
- `approve-requisitions` (requisitions module)
- `issue-requisitions` (requisitions module)
- `approve-returns` (returns module)
- `clear-purchase-orders` (purchase-orders module)
- `export-reports` (reports module)

### Middleware

#### CheckPermission Middleware

**Location**: `app/Http/Middleware/CheckPermission.php`

```php
public function handle(Request $request, Closure $next, ...$permissions): Response
{
    if (!Auth::check()) {
        return redirect('login')->with('error', 'Please login to continue.');
    }

    $user = Auth::user();

    // Admin role bypasses all permission checks
    if ($user->hasRole('admin')) {
        return $next($request);
    }

    // Check if user has at least one of the required permissions
    foreach ($permissions as $permission) {
        if ($user->hasPermission($permission)) {
            return $next($request);
        }
    }

    abort(403, 'You do not have permission to access this resource. Please Contact IT Admin.');
}
```

**Usage in routes**:
```php
Route::middleware(['permission:view-users,create-users,edit-users,delete-users'])
    ->group(function () {
        Route::resource('users', UserController::class);
    });

Route::middleware(['permission:approve-requisitions'])
    ->post('requisitions/{id}/approve', [RequisitionApprovalController::class, 'approve']);
```

#### CheckRole Middleware

**Location**: `app/Http/Middleware/CheckRole.php`

```php
public function handle(Request $request, Closure $next, ...$roles): Response
{
    if (!Auth::check()) {
        return redirect('login');
    }

    $user = Auth::user();

    foreach ($roles as $role) {
        if ($user->hasRole($role)) {
            return $next($request);
        }
    }

    abort(403, 'Unauthorized action.');
}
```

**Usage in routes**:
```php
Route::middleware(['role:admin'])->prefix('admin')->group(function () {
    // All admin routes
});
```

### Dual Permission System

The application supports both:

1. **Role-based permissions** (via `permission_role` pivot)
2. **Direct user permissions** (via `permission_user` pivot)

**Permission Check Flow**:
```
User → getAllPermissions()
         ├── Role Permissions (from all assigned roles)
         └── Direct Permissions (user-specific overrides)
              └── Merge & Return Unique
```

**User Model Methods**:
```php
// Get all permissions from roles
public function getAllPermissions()
{
    // Get direct permissions
    $directPermissions = $this->permissions;

    // Get permissions from all roles
    $rolePermissions = collect();
    foreach ($this->roles as $role) {
        $rolePermissions = $rolePermissions->merge($role->permissions);
    }

    // Merge and return unique
    return $directPermissions->merge($rolePermissions)->unique('id');
}

// Check if user has permission
public function hasPermission($permission)
{
    return $this->getAllPermissions()->contains('name', $permission);
}

// Check if user has any of given permissions
public function hasAnyPermission($permissions)
{
    foreach ($permissions as $permission) {
        if ($this->hasPermission($permission)) {
            return true;
        }
    }
    return false;
}
```

### Default Roles

Seeded by `RolePermissionSeeder`:

#### Admin Role
- **Permissions**: ALL (43 permissions)
- **Access**: Full system access
- **Special**: Bypasses permission middleware
- **Default User**: admin@lanmic.com / password

#### Manager Role
- **Permissions**:
  - View dashboard
  - Department/Sub-dept/Division management
  - Approve/Issue requisitions
  - Approve returns
  - View/Clear purchase orders
  - View/Export all reports
- **Access**: Admin panel + approval workflows
- **Default User**: manager@lanmic.com / password

#### User Role
- **Permissions**:
  - View dashboard
  - Create/View own requisitions
  - Create/View own returns
  - View reports
- **Access**: User portal only
- **Default User**: user@lanmic.com / password

### Permission Assignment Workflow

1. **Admin creates user and assigns role**
   - Role permissions automatically assigned as direct permissions
   - Done via `UserController::autoAssignPermissionsFromRoles()`

2. **Admin can modify user permissions**
   - Route: `/admin/users/{user}/permissions`
   - Can add/remove permissions beyond role
   - Role permissions pre-ticked but editable
   - Changes saved to `permission_user` table

3. **Sidebar dynamic menu**
   - Each menu item checks user permissions
   - Hidden if user lacks required permission
   - Implemented in `resources/views/layouts/partials/sidebar.blade.php`

---

## 8. Complete Workflows

### Workflow 1: Standard Requisition (Items in Stock)

```
┌─────────────────────────────────────────────────────────────┐
│ PHASE 1: User Creates Requisition                           │
└─────────────────────────────────────────────────────────────┘
    ├─ User: /requisitions/create
    ├─ Selects: Department → Sub-Department → Division
    ├─ Searches SAGE items
    ├─ For each item:
    │    ├─ Selects location
    │    ├─ Enters quantity
    │    ├─ API: Check availability (ItemAvailabilityService)
    │    │    └─ Available = SAGE Stock - Pending Requisitions
    │    └─ If available:
    │         ├─ Add to requisition_items
    │         └─ If qty > available:
    │              ├─ Add available qty to requisition_items
    │              └─ Add (qty - available) to purchase_order_items
    └─ Submit
         └─ Creates: Requisition (approve_status=pending, clear_status=pending)

┌─────────────────────────────────────────────────────────────┐
│ PHASE 2: Admin Approves Requisition                         │
└─────────────────────────────────────────────────────────────┘
    ├─ Admin: /admin/requisitions
    ├─ Views: Requisition details
    │    ├─ User info
    │    ├─ Department/Sub-dept/Division
    │    ├─ Items list with quantities
    │    └─ Notes
    ├─ Action: Approve or Reject
    │    ├─ If Approve:
    │    │    └─ Update: approve_status=approved, approved_by, approved_at
    │    └─ If Reject:
    │         ├─ Enter: rejection_reason
    │         └─ Update: approve_status=rejected, mark items as rejected
    └─ Redirect to requisitions list

┌─────────────────────────────────────────────────────────────┐
│ PHASE 3: Admin Issues Items                                 │
└─────────────────────────────────────────────────────────────┘
    ├─ Admin: /admin/requisitions/{id}/issue-items
    ├─ For each item:
    │    ├─ Load SAGE locations with quantities
    │    ├─ Calculate: available = SAGE qty - pending
    │    └─ Display: Item, Remaining Qty, Locations with Available Qty
    ├─ For each item to issue:
    │    ├─ Select: Location
    │    ├─ Enter: Quantity (≤ available)
    │    ├─ Enter: Notes (optional)
    │    └─ Add to issue list
    ├─ Submit
    │    └─ For each issue:
    │         ├─ Validate: qty ≤ available stock
    │         ├─ SAGE: POST IC/ICAdjustments
    │         │    ├─ TransactionType: BothDecrease
    │         │    ├─ ItemNumber, Location, Quantity
    │         │    └─ Response: UnitCost, SequenceNumber, BatchNumber
    │         ├─ Create: RequisitionIssuedItem
    │         │    ├─ item details, location, quantity
    │         │    ├─ unit_price = SAGE UnitCost
    │         │    ├─ total_price = unit_price × quantity
    │         │    ├─ reference_number_1 = SequenceNumber
    │         │    ├─ reference_number_2 = BatchNumber
    │         │    └─ issued_by, issued_at
    │         └─ Check: All items fully issued?
    │              └─ If yes:
    │                   └─ Update: clear_status=cleared, cleared_by, cleared_at
    └─ Success: Items issued and SAGE updated

┌─────────────────────────────────────────────────────────────┐
│ PHASE 4: User Views Issued Items                            │
└─────────────────────────────────────────────────────────────┘
    ├─ User: /requisitions/{id}
    ├─ Views:
    │    ├─ Requisition details
    │    ├─ Issued items grouped by item code
    │    │    ├─ Issued quantity
    │    │    ├─ Unit price
    │    │    ├─ Total price
    │    │    ├─ Location
    │    │    └─ SAGE references
    │    └─ Remaining items to be issued
    └─ Can: Create return from this requisition
```

### Workflow 2: Requisition with Purchase Order

```
┌─────────────────────────────────────────────────────────────┐
│ PHASE 1: User Creates Requisition (Out of Stock)            │
└─────────────────────────────────────────────────────────────┘
    ├─ Same as Workflow 1, but:
    │    └─ For items where qty > available:
    │         ├─ Add available to requisition_items (can be 0)
    │         └─ Add (qty - available) to purchase_order_items
    │              └─ status=pending
    └─ Result:
         ├─ Requisition: approve_status=pending
         ├─ Requisition Items: Available quantities
         └─ PO Items: Quantities that need purchasing

┌─────────────────────────────────────────────────────────────┐
│ PHASE 2-3: Admin Approves and Issues Available Items        │
└─────────────────────────────────────────────────────────────┘
    └─ Same as Workflow 1
         └─ Only available items get issued
              └─ Requisition: clear_status remains 'pending'

┌─────────────────────────────────────────────────────────────┐
│ PHASE 4: Admin Manages Purchase Orders                      │
└─────────────────────────────────────────────────────────────┘
    ├─ Admin: /admin/purchase-orders
    ├─ Views: PO items grouped by:
    │    ├─ By Requisition:
    │    │    └─ All PO items per requisition
    │    └─ By Item:
    │         └─ Total quantity needed per item across all requisitions
    ├─ Filters:
    │    ├─ Status: pending | cleared
    │    ├─ Date range
    │    └─ Item code
    ├─ Actions:
    │    ├─ Clear single PO item
    │    ├─ Bulk clear by requisition
    │    └─ Bulk clear by item code
    └─ When cleared:
         ├─ Update: status=cleared, cleared_by, cleared_at
         └─ Note: Items still need to be physically received to SAGE
              └─ Once in SAGE, admin can issue them (Workflow 1 Phase 3)

┌─────────────────────────────────────────────────────────────┐
│ PHASE 5: Admin Issues PO Items (After Physical Receipt)     │
└─────────────────────────────────────────────────────────────┘
    ├─ Physical items received and entered in SAGE manually
    ├─ Admin: /admin/requisitions/{id}/issue-items
    └─ Issue PO items following normal issue workflow
         └─ When all items issued:
              └─ Requisition: clear_status=cleared
```

### Workflow 3: Item Return (Same Condition)

```
┌─────────────────────────────────────────────────────────────┐
│ PHASE 1: User Creates Return                                │
└─────────────────────────────────────────────────────────────┘
    ├─ User: /returns/create
    ├─ Select: Requisition (approved, with issued items)
    ├─ API: Load issued items for requisition
    │    └─ Calculate: max_return_qty = issued_qty - already_returned
    ├─ For each item to return:
    │    ├─ Select: Issued item
    │    ├─ Select: Return type ('same' = good condition)
    │    ├─ Enter: Quantity (≤ max_return_qty)
    │    ├─ Enter: Location code (where to return)
    │    └─ Enter: Notes (optional)
    ├─ Submit
    │    └─ Creates:
    │         ├─ Return (status=pending, requisition_id, returned_by, returned_at)
    │         └─ Return Items (approve_status=pending for each)
    └─ Success: Return created, awaiting admin approval

┌─────────────────────────────────────────────────────────────┐
│ PHASE 2: Admin Processes Return                             │
└─────────────────────────────────────────────────────────────┘
    ├─ Admin: /admin/returns
    ├─ Views: Return with items
    │    ├─ Return info (user, date, requisition)
    │    └─ Items list (item details, return_type, quantity, location)
    ├─ Admin: /admin/returns/{id}/approve-items
    ├─ For each return item:
    │    ├─ Review: Item condition, return type
    │    ├─ Get: Unit price from SAGE
    │    ├─ Decide split:
    │    │    ├─ GRN Quantity (good to return to stock)
    │    │    └─ Scrap Quantity (damaged/unusable)
    │    │    └─ Rule: GRN Qty + Scrap Qty = Return Qty
    │    ├─ Can modify:
    │    │    ├─ Item code (if wrong item)
    │    │    └─ Location code (if different return location)
    │    └─ Enter: Admin note (optional)
    ├─ Submit
    │    └─ For each item:
    │         ├─ IF GRN Quantity > 0:
    │         │    ├─ SAGE: POST IC/ICAdjustments
    │         │    │    ├─ TransactionType: BothIncrease
    │         │    │    ├─ ItemNumber, Location, Quantity, UnitCost
    │         │    │    └─ Response: UnitCost, SequenceNumber, BatchNumber
    │         │    └─ Create: GrnItem
    │         │         ├─ item details, location, grn_quantity
    │         │         ├─ unit_price, total_price
    │         │         ├─ reference_number_1, reference_number_2
    │         │         └─ processed_by, processed_at
    │         ├─ IF Scrap Quantity > 0:
    │         │    └─ Create: ScrapItem (no SAGE posting)
    │         │         ├─ item details, location, scrap_quantity
    │         │         ├─ unit_price, total_price
    │         │         └─ processed_by, processed_at
    │         ├─ Update: ReturnItem.approve_status
    │         │    ├─ 'approved' = all GRN
    │         │    ├─ 'rejected' = all Scrap
    │         │    └─ 'partial' = both GRN and Scrap
    │         └─ Update: ReturnItem.approved_by, approved_at, admin_note
    │    └─ When all items processed:
    │         └─ Update: Return.status=cleared
    └─ Success: Return processed, SAGE updated (for GRN)

┌─────────────────────────────────────────────────────────────┐
│ PHASE 3: User Views Return Status                           │
└─────────────────────────────────────────────────────────────┘
    ├─ User: /returns/{id}
    ├─ Views:
    │    ├─ Return details
    │    ├─ Return items with approve status
    │    ├─ GRN items (returned to stock)
    │    │    ├─ Quantity, location
    │    │    ├─ Unit price, total price
    │    │    └─ SAGE references
    │    └─ Scrap items (written off)
    │         ├─ Quantity, location
    │         └─ Unit price, total price
    └─ Can see: Financial impact of return
```

### Workflow 4: Item Return (Used Condition)

```
Same as Workflow 3, except:
    ├─ PHASE 1:
    │    └─ User selects: return_type='used' (may be damaged)
    └─ PHASE 2:
         └─ Admin decision on GRN vs Scrap more critical
              ├─ Inspect item condition
              ├─ If usable: GRN (back to stock)
              └─ If unusable: Scrap (write-off)
```

---

## 9. API Endpoints

### Internal API Endpoints (Laravel)

**Requisition APIs**:
```
GET  /api/departments/{id}/sub-departments
     - Get sub-departments for department
     - Returns: JSON array of sub-departments

GET  /api/sub-departments/{id}/divisions
     - Get divisions for sub-department
     - Returns: JSON array of divisions

GET  /api/items/{itemCode}/availability?location_code={loc}&requisition_id={id}
     - Get item availability at location
     - Returns: { available: number, message: string }

GET  /api/requisitions/pending-items
     - Get all pending items across system (for FIFO display)
     - Returns: { item_code: pending_quantity, ... }
```

**Return APIs**:
```
GET  /api/requisitions/{id}/issued-items
     - Get issued items for requisition
     - Returns: JSON array with issued items and max_return_qty
```

### SAGE 300 API Endpoints

**Base URL**: `http://192.168.11.60/Sage300WebApi/v1.0/-/SAMINC`
**Authentication**: Basic Auth (username/password)

**Item Endpoints**:
```
GET  IC/ICItems
     - Get all items
     - Returns: Array of item objects

GET  IC/ICItems('{itemCode}')
     - Get single item
     - Returns: Item object with full details

GET  IC/ICItemPricing?$filter=UnformattedItemNumber eq '{itemCode}'
     - Get item pricing details
     - Returns: Pricing information
```

**Location Endpoints**:
```
GET  IC/ICLocations
     - Get all locations
     - Returns: Array of location objects

GET  IC/ICLocations?$filter=LocationKey eq '{locationCode}'
     - Get single location
     - Returns: Location object

GET  IC/ICLocationDetails?$filter=ItemNumber eq '{itemCode}'
     - Get item quantities at all locations
     - Returns: Array of location details with quantities

GET  IC/ICLocationDetails(ItemNumber='{itemCode}',Location='{locationCode}')
     - Get item quantity at specific location
     - Returns: {
         QuantityOnHand: number,
         QuantityAvailable: number,
         UnitCost: number,
         ...
       }
```

**Inventory Adjustment Endpoint**:
```
POST IC/ICAdjustments
     - Create inventory adjustment
     - Body:
       {
         "TransactionType": "BothDecrease" | "BothIncrease",
         "Details": [{
           "ItemNumber": "ITEM001",
           "Location": "MAIN",
           "Quantity": 10,
           "UnitCost": 25.50  // Required for BothIncrease only
         }],
         "Comment": "Requisition: REQ-202401-0001"
       }
     - Returns:
       {
         "SequenceNumber": 123456,
         "BatchNumber": "ICA000001",
         "UnitCost": 25.50,
         ...
       }
```

### SAGE 300 Transaction Types

**BothDecrease** (Item Issuing):
- Decreases quantity on hand
- Decreases total cost value
- Does NOT require UnitCost input
- System uses weighted average cost
- Returns actual UnitCost used

**BothIncrease** (GRN Processing):
- Increases quantity on hand
- Increases total cost value
- REQUIRES UnitCost input
- Admin provides cost value
- Returns confirmed UnitCost

---

## 10. Deployment Guide

### System Requirements

- **PHP**: 8.2 or higher
- **Web Server**: Apache/Nginx
- **Database**: SQL Server 2016+ (or MySQL/PostgreSQL with config changes)
- **Extensions**:
  - php-pdo-sqlsrv (for SQL Server)
  - php-mbstring
  - php-xml
  - php-curl
  - php-zip
  - php-gd

### Environment Setup

1. **Clone Repository**:
   ```bash
   git clone <repository-url>
   cd lanmic-requisition-system
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Edit `.env` File**:
   ```env
   APP_NAME="Lanmic Requisition System"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com

   DB_CONNECTION=sqlsrv
   DB_HOST=your-db-host
   DB_PORT=1433
   DB_DATABASE=lanmic_internal
   DB_USERNAME=your-db-user
   DB_PASSWORD=your-db-password

   SAGE300_BASE_URL=http://192.168.11.60/Sage300WebApi/v1.0/-/SAMINC
   SAGE300_USERNAME=WEBUSER
   SAGE300_PASSWORD=Webuser
   SAGE300_TIMEOUT=30
   ```

5. **Database Setup**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=RolePermissionSeeder
   ```

6. **Storage & Cache**:
   ```bash
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

7. **Build Assets**:
   ```bash
   npm run build
   ```

8. **Set Permissions**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

### Web Server Configuration

**Apache** (`.htaccess` included):
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/lanmic-requisition-system/public

    <Directory /path/to/lanmic-requisition-system/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/lanmic-error.log
    CustomLog ${APACHE_LOG_DIR}/lanmic-access.log combined
</VirtualHost>
```

**Nginx**:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/lanmic-requisition-system/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Default User Accounts

After seeding, three default users are created:

| Email                | Password | Role    |
|----------------------|----------|---------|
| admin@lanmic.com     | password | Admin   |
| manager@lanmic.com   | password | Manager |
| user@lanmic.com      | password | User    |

**IMPORTANT**: Change all passwords immediately in production!

### Post-Deployment Checklist

- [ ] Change all default passwords
- [ ] Verify SAGE 300 API connectivity
- [ ] Test requisition creation workflow
- [ ] Test issuing workflow with SAGE posting
- [ ] Test return workflow with GRN/Scrap
- [ ] Verify permission system
- [ ] Test all reports and exports
- [ ] Configure backup schedule
- [ ] Set up monitoring/logging
- [ ] SSL certificate installed
- [ ] Firewall rules configured

### Backup Strategy

**Database**:
```bash
# Daily backup
php artisan backup:run --only-db

# Full backup (DB + files)
php artisan backup:run
```

**Manual SQL Server Backup**:
```sql
BACKUP DATABASE lanmic_internal
TO DISK = 'C:\Backups\lanmic_internal.bak'
WITH FORMAT, MEDIANAME = 'SQLServerBackups', NAME = 'Full Backup';
```

### Troubleshooting

**SAGE 300 Connection Issues**:
```bash
# Test connectivity
curl -u WEBUSER:Webuser http://192.168.11.60/Sage300WebApi/v1.0/-/SAMINC/IC/ICItems

# Check Laravel logs
tail -f storage/logs/laravel.log
```

**Permission Issues**:
```bash
# Reset permissions
php artisan cache:clear
php artisan config:clear
php artisan view:clear
chmod -R 775 storage bootstrap/cache
```

**Database Connection Issues**:
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check SQL Server drivers
php -m | grep pdo_sqlsrv
```

---

## Appendix A: Database Diagram

```
┌─────────────────────┐
│       users         │
├─────────────────────┤
│ id                  │◄────────┐
│ name                │         │
│ email               │         │
│ password            │         │
└─────────────────────┘         │
         △                      │
         │                      │
         │                      │
    ┌────┴─────┐                │
    │          │                │
┌───▼───┐  ┌───▼────┐           │
│ roles │  │permissions│         │
└───┬───┘  └───┬────┘           │
    │          │                │
    └────┬─────┘                │
         │                      │
┌────────▼──────────────────────┼────────────────┐
│           requisitions        │                │
├───────────────────────────────┼────────────────┤
│ id                            │                │
│ requisition_number            │                │
│ user_id (FK)──────────────────┘                │
│ department_id (FK)                             │
│ approve_status                                 │
│ clear_status                                   │
└────────┬──────────────────────────────────────┘
         │
    ┌────┴─────┬────────────────┬─────────────┐
    │          │                │             │
┌───▼────┐ ┌──▼────────┐ ┌─────▼─────┐ ┌────▼────┐
│requisition│requisition│purchase    │ │returns  │
│  items   │issued_items│order_items │ │         │
└──────────┘ └─────┬────┘ └──────────┘ └────┬────┘
                   │                        │
                   │                   ┌────┴────┐
                   │                   │return   │
                   │                   │ items   │
                   │                   └────┬────┘
                   │                        │
                   │                   ┌────┴────┬────────┐
                   │                   │         │        │
                   │                ┌──▼──┐  ┌──▼───┐    │
                   │                │ grn │  │scrap │    │
                   │                │items│  │items │    │
                   │                └─────┘  └──────┘    │
                   └─────────────────────────────────────┘
```

---

## Appendix B: Permission Matrix

| Permission                 | Admin | Manager | User |
|----------------------------|-------|---------|------|
| view-dashboard             | ✓     | ✓       | ✓    |
| view-users                 | ✓     | ✓       |      |
| create-users               | ✓     |         |      |
| edit-users                 | ✓     |         |      |
| delete-users               | ✓     |         |      |
| assign-user-permissions    | ✓     |         |      |
| view-roles                 | ✓     | ✓       |      |
| create-roles               | ✓     |         |      |
| edit-roles                 | ✓     |         |      |
| delete-roles               | ✓     |         |      |
| view-permissions           | ✓     | ✓       |      |
| create-permissions         | ✓     |         |      |
| edit-permissions           | ✓     |         |      |
| delete-permissions         | ✓     |         |      |
| view-departments           | ✓     | ✓       |      |
| create-departments         | ✓     | ✓       |      |
| edit-departments           | ✓     | ✓       |      |
| delete-departments         | ✓     | ✓       |      |
| view-sub-departments       | ✓     | ✓       |      |
| create-sub-departments     | ✓     | ✓       |      |
| edit-sub-departments       | ✓     | ✓       |      |
| delete-sub-departments     | ✓     | ✓       |      |
| view-divisions             | ✓     | ✓       |      |
| create-divisions           | ✓     | ✓       |      |
| edit-divisions             | ✓     | ✓       |      |
| delete-divisions           | ✓     | ✓       |      |
| view-requisitions          | ✓     | ✓       | ✓    |
| create-requisitions        | ✓     | ✓       | ✓    |
| edit-requisitions          | ✓     |         |      |
| delete-requisitions        | ✓     |         |      |
| approve-requisitions       | ✓     | ✓       |      |
| issue-requisitions         | ✓     | ✓       |      |
| view-purchase-orders       | ✓     | ✓       |      |
| clear-purchase-orders      | ✓     | ✓       |      |
| view-returns               | ✓     | ✓       | ✓    |
| create-returns             | ✓     | ✓       | ✓    |
| edit-returns               | ✓     |         |      |
| delete-returns             | ✓     |         |      |
| approve-returns            | ✓     | ✓       |      |
| view-reports               | ✓     | ✓       | ✓    |
| export-reports             | ✓     | ✓       |      |
| view-settings              | ✓     |         |      |
| edit-settings              | ✓     |         |      |

---

## Appendix C: File Locations Reference

### Controllers
- User Requisitions: `app/Http/Controllers/RequisitionController.php`
- User Returns: `app/Http/Controllers/ReturnController.php`
- Admin Requisitions: `app/Http/Controllers/Admin/RequisitionApprovalController.php`
- Admin Returns: `app/Http/Controllers/Admin/ReturnApprovalController.php`
- Admin POs: `app/Http/Controllers/Admin/PurchaseOrderController.php`
- Reports: `app/Http/Controllers/Admin/ReportController.php`
- Users/Roles/Permissions: `app/Http/Controllers/Admin/UserController.php` (etc.)

### Models
- `app/Models/User.php`
- `app/Models/Requisition.php`
- `app/Models/RequisitionItem.php`
- `app/Models/RequisitionIssuedItem.php`
- `app/Models/PurchaseOrderItem.php`
- `app/Models/ReturnModel.php`
- `app/Models/ReturnItem.php`
- `app/Models/GrnItem.php`
- `app/Models/ScrapItem.php`

### Services
- SAGE 300: `app/Services/Sage300Service.php`
- Availability: `app/Services/ItemAvailabilityService.php`

### Middleware
- `app/Http/Middleware/CheckRole.php`
- `app/Http/Middleware/CheckPermission.php`

### Views
- User Portal: `resources/views/requisitions/`, `resources/views/returns/`
- Admin Panel: `resources/views/admin/requisitions/`, `resources/views/admin/returns/`, etc.
- Layouts: `resources/views/layouts/`
- Components: `resources/views/components/`

### Migrations
- All in: `database/migrations/`
- Key migrations:
  - `*_create_users_table.php`
  - `*_create_roles_table.php`
  - `*_create_permissions_table.php`
  - `*_create_requisitions_table.php`
  - `*_create_return_items_table.php`
  - etc.

### Configuration
- SAGE 300: `config/sage300.php`
- Database: `config/database.php`
- Application: `config/app.php`

### Routes
- All routes: `routes/web.php`
- API routes: Included in `routes/web.php` under `/api` prefix

---

**End of Technical Documentation**

For user-facing instructions, refer to the USER_GUIDE.md document.
