# LANMIC INTERNAL REQUISITION MANAGEMENT SYSTEM
## User Guide

---

## Table of Contents
1. [Getting Started](#getting-started)
2. [User Roles](#user-roles)
3. [Dashboard Overview](#dashboard-overview)
4. [Managing Requisitions (Users)](#managing-requisitions-users)
5. [Managing Returns (Users)](#managing-returns-users)
6. [Admin: Managing Users & Permissions](#admin-managing-users--permissions)
7. [Admin: Managing Organization](#admin-managing-organization)
8. [Admin: Approving Requisitions](#admin-approving-requisitions)
9. [Admin: Issuing Items](#admin-issuing-items)
10. [Admin: Processing Returns](#admin-processing-returns)
11. [Admin: Managing Purchase Orders](#admin-managing-purchase-orders)
12. [Reports & Analytics](#reports--analytics)
13. [Troubleshooting & FAQs](#troubleshooting--faqs)

---

## 1. Getting Started

### Accessing the System

1. Open your web browser and navigate to the system URL
2. You will see the login page
3. Enter your email and password
4. Click "Login"

### First Time Login

If this is your first time logging in:
- Your administrator will provide your username (email) and temporary password
- After logging in, click on your name in the top right corner
- Select "Profile" and change your password

### Password Reset

If you forgot your password:
1. Click "Forgot Password?" on the login page
2. Enter your email address
3. Check your email for a password reset link
4. Click the link and set a new password

---

## 2. User Roles

The system has three main user roles:

### User Role
**What you can do:**
- View the dashboard
- Create item requisitions
- View your own requisitions
- Delete pending requisitions
- Create item returns
- View your own returns
- Delete pending returns
- View reports

**What you CANNOT do:**
- Approve requisitions
- Issue items
- Process returns
- Manage users or settings
- Clear purchase orders

### Manager Role
**What you can do (in addition to User permissions):**
- Approve requisitions from users
- Issue items from inventory
- Approve and process returns
- View and clear purchase orders
- Manage departments, sub-departments, and divisions
- Export reports

**What you CANNOT do:**
- Create or modify users
- Assign permissions
- Change system settings

### Admin Role
**What you can do:**
- **Everything** - Full system access
- Manage users, roles, and permissions
- Configure departments and organizational structure
- Approve and issue requisitions
- Process returns
- Manage purchase orders
- Access all reports
- Configure system settings

---

## 3. Dashboard Overview

After logging in, you'll see the dashboard with key information:

### User Dashboard

**Statistics Cards:**
- **My Requisitions**: Total number of your requisitions
  - Pending (awaiting approval)
  - Approved
  - Rejected
- **My Returns**: Total number of your returns
  - Pending (awaiting processing)
  - Cleared (processed)
- **Recent Activity**: Your recent requisitions and returns

**Quick Actions:**
- Create New Requisition
- Create New Return
- View All Requisitions
- View All Returns

### Admin/Manager Dashboard

**Additional Statistics:**
- **Pending Approvals**: Requisitions awaiting approval
- **Items to Issue**: Approved requisitions with items to issue
- **Returns to Process**: Returns awaiting admin processing
- **Purchase Orders**: Items waiting for purchase
- **System Overview**: Total users, departments, items

---

## 4. Managing Requisitions (Users)

### Creating a New Requisition

A requisition is a request for items from inventory.

**Step-by-Step:**

1. **Navigate to Requisitions**
   - Click "Requisitions" in the sidebar
   - Click "Create Requisition" button

2. **Select Organization Details**
   - **Department**: Select your department from dropdown
   - **Sub-Department**: After selecting department, choose sub-department
   - **Division**: After selecting sub-department, choose division
   - **Notes** (Optional): Add any additional information

3. **Add Items to Requisition**

   For each item you need:

   a. **Search for Item:**
      - Type item name or code in the search box
      - Select item from dropdown

   b. **Select Location:**
      - Choose the location where the item should come from
      - The system will show available quantity at that location

   c. **Enter Quantity:**
      - Enter the quantity you need
      - If you request more than available:
        - Available quantity will be added to requisition
        - Remaining quantity will create a Purchase Order

   d. **Add Specifications** (Optional):
      - Enter any special requirements or notes for this item

   e. **Click "Add Item"**
      - Item will be added to your requisition list

   f. **Repeat** for all items needed

4. **Review Your Requisition**
   - Check all items in the list
   - Verify quantities and specifications
   - Remove any item by clicking the "Remove" button

5. **Submit Requisition**
   - Click "Submit Requisition"
   - You'll see a confirmation message
   - Your requisition is now pending admin approval

**Understanding Item Availability:**

- **Green message**: "Available: X units" - Item is in stock
- **Yellow/Orange message**: "Partially available" - Some stock, rest will be PO
- **Red message**: "Out of stock - will create PO" - No current stock

**Purchase Orders (PO):**
- If requested quantity exceeds available stock, a PO is automatically created
- PO items will be issued after admin marks them as received
- You'll be notified when PO items are available

### Viewing Your Requisitions

1. Click "Requisitions" → "My Requisitions"
2. You'll see a list with:
   - **Requisition Number**: e.g., REQ-202401-0001
   - **Date Created**
   - **Status**: Pending, Approved, Rejected
   - **Department/Sub-dept/Division**
   - **Total Items**
   - **Actions**: View, Delete (if pending)

3. **Filter Requisitions:**
   - By Status: Pending, Approved, Rejected
   - By Date Range: Start date to end date
   - Click "Filter" to apply

### Viewing Requisition Details

1. Click "View" button on any requisition
2. You'll see:
   - **Requisition Information:**
     - Number, date, status
     - Department, sub-department, division
     - Your notes

   - **Requested Items:**
     - Item name, code, category
     - Requested quantity
     - Location
     - Specifications

   - **Issued Items** (if approved and issued):
     - Issued quantity
     - Unit price
     - Total price
     - Issue location
     - Issue date
     - SAGE reference numbers

   - **Purchase Order Items** (if any):
     - Items that need to be purchased
     - Status: Pending or Cleared

### Deleting a Requisition

**You can only delete PENDING requisitions.**

1. Go to "My Requisitions"
2. Find the pending requisition
3. Click "Delete" button
4. Confirm deletion
5. Requisition and all its items will be deleted

**Note:** You cannot delete approved or rejected requisitions.

---

## 5. Managing Returns (Users)

### When to Create a Return

Create a return when:
- You received the wrong item
- You received more than needed
- Item is damaged or defective
- Incorrect data entry in original requisition

### Creating a New Return

**Step-by-Step:**

1. **Navigate to Returns**
   - Click "Returns" in the sidebar
   - Click "Create Return" button

2. **Select Requisition** (Optional but recommended)
   - Choose the requisition you're returning items from
   - This helps track return history
   - If not related to a specific requisition, leave blank

3. **Add Items to Return**

   For each item to return:

   a. **Select Issued Item:**
      - If you selected a requisition, choose from issued items
      - Shows item name, code, issued quantity, and already returned

   b. **Select Return Type:**
      - **Same Condition**: Item is unused, in original condition
      - **Used**: Item has been used or may be damaged

   c. **Enter Return Quantity:**
      - Enter quantity you're returning
      - Cannot exceed: Issued Quantity - Already Returned
      - System shows maximum returnable quantity

   d. **Enter Return Location:**
      - Enter the location code where item should be returned
      - Usually the original issue location

   e. **Add Notes** (Optional):
      - Explain reason for return
      - Describe item condition
      - Any other relevant information

   f. **Click "Add to Return"**
      - Item will be added to your return list

   g. **Repeat** for all items to return

4. **Review Your Return**
   - Check all items in the return list
   - Verify quantities and locations
   - Remove any item if needed

5. **Submit Return**
   - Click "Submit Return"
   - You'll see a confirmation message
   - Return is now pending admin processing

**Important Notes:**
- You can only return items that have been issued to you
- Returns must be approved by admin before items go back to stock
- Admin may split returns into:
  - **GRN** (Goods Return Note): Good items returned to stock
  - **Scrap**: Damaged items written off

### Viewing Your Returns

1. Click "Returns" → "My Returns"
2. You'll see a list with:
   - **Return Date**
   - **Related Requisition** (if any)
   - **Status**: Pending, Cleared
   - **Total Items**
   - **Actions**: View, Delete (if pending)

3. **Filter Returns:**
   - By Status: Pending, Cleared
   - By Date Range
   - Click "Filter" to apply

### Viewing Return Details

1. Click "View" button on any return
2. You'll see:
   - **Return Information:**
     - Date, status
     - Related requisition
     - Returned by (you)

   - **Return Items:**
     - Item name, code
     - Return quantity
     - Return type (Same/Used)
     - Location
     - Your notes
     - **Approval Status**: Pending, Approved, Rejected, Partial
     - Admin notes (if any)

   - **GRN Items** (if processed):
     - Items returned to stock
     - GRN quantity
     - Location
     - Unit price, total price
     - SAGE reference numbers

   - **Scrap Items** (if any):
     - Items written off as damaged
     - Scrap quantity
     - Unit price, total price

**Understanding Approval Status:**
- **Pending**: Awaiting admin processing
- **Approved**: All items returned to stock (GRN)
- **Rejected**: All items scrapped (written off)
- **Partial**: Some items GRN, some items scrapped

### Deleting a Return

**You can only delete PENDING returns.**

1. Go to "My Returns"
2. Find the pending return
3. Click "Delete" button
4. Confirm deletion
5. Return and all its items will be deleted

---

## 6. Admin: Managing Users & Permissions

### Viewing Users

1. Navigate to "Authentication" → "Users"
2. You'll see a list of all users:
   - Name, email
   - Assigned roles
   - Status (Active/Inactive)
   - Actions: View, Edit, Delete, Manage Permissions

### Creating a New User

1. Click "Create User" button
2. Fill in the form:
   - **Name**: Full name
   - **Email**: Must be unique
   - **Password**: Strong password (min 8 characters)
   - **Confirm Password**: Re-enter password
   - **Status**: Active or Inactive
   - **Roles**: Select one or more roles (Admin, Manager, User)
3. Click "Create User"
4. User will receive login credentials

**Auto-Permission Assignment:**
- When you assign a role to a user, all permissions from that role are automatically assigned to the user
- You can modify these permissions later user-by-user

### Editing User Details

1. Find the user in the list
2. Click "Edit" button
3. Modify:
   - Name
   - Email
   - Status
   - Roles
4. Click "Update User"

**Changing User Password:**
1. Edit user
2. Enter new password in "Password" field
3. Leave blank if not changing password
4. Click "Update User"

### Managing User Permissions

This allows you to customize permissions for individual users beyond their role.

**Step-by-Step:**

1. Find the user in the list
2. Click the **Key icon** (🔑) button
3. You'll see the **Permission Assignment** page:

**Understanding the Permission Page:**

- **User Info Section** (Left):
  - User name, email
  - Assigned roles
  - Legend:
    - 🟢 **Green badges**: Permissions from roles (pre-ticked)
    - 🔵 **Blue badges**: Direct permissions

- **Permission Modules** (Right):
  - Organized by module (Dashboard, Users, Requisitions, etc.)
  - Each permission has a checkbox
  - Green checkboxes: From user's role
  - All checkboxes are editable

**Assigning/Removing Permissions:**

1. **To add a permission:**
   - Check the checkbox next to the permission
   - Permission will be added (blue badge)

2. **To remove a permission:**
   - Uncheck the checkbox
   - Even role permissions can be removed for this user

3. **Click "Update Permissions"**
   - Changes are saved
   - User's access will update immediately

**Use Cases:**
- Give a User extra permissions temporarily
- Restrict a Manager from certain approvals
- Create custom permission sets per user

### Managing Roles

1. Navigate to "Authentication" → "Roles"

**Viewing Roles:**
- Lists all roles with description
- Shows permission count for each role

**Creating a Role:**
1. Click "Create Role"
2. Enter:
   - Role Name (e.g., "Warehouse Manager")
   - Description
   - Status (Active)
3. Click "Create Role"
4. Then assign permissions to the role

**Editing Role Permissions:**
1. Click "Edit" on a role
2. Select permissions for this role
3. Organized by module
4. Click "Update Role"

**Deleting a Role:**
- Click "Delete" button
- Confirm deletion
- Users with this role will lose role permissions (but keep direct permissions)

### Managing Permissions

1. Navigate to "Authentication" → "Permissions"

**Viewing Permissions:**
- Lists all permissions organized by module
- Shows permission name and description

**Creating a Permission:**
1. Click "Create Permission"
2. Enter:
   - Permission Name (e.g., "view-inventory")
   - Module (e.g., "inventory")
   - Description
   - Status (Active)
3. Click "Create Permission"

**Note:** Creating new permissions requires updating code to actually enforce them.

---

## 7. Admin: Managing Organization

### Managing Departments

Departments are the top-level organizational units.

**Viewing Departments:**
1. Navigate to "Organization" → "Departments"
2. View list with:
   - Department name
   - Short code
   - Linked sub-departments
   - Status

**Creating a Department:**
1. Click "Create Department"
2. Fill in:
   - **Name**: Full department name
   - **Short Code**: Unique abbreviation (e.g., "IT", "HR")
   - **Description**: What this department does
   - **Sub-Departments**: Select related sub-departments
   - **Status**: Active
3. Click "Create Department"

**Linking Sub-Departments:**
- A department can have multiple sub-departments
- A sub-department can belong to multiple departments
- Select from existing sub-departments when creating/editing

**Editing a Department:**
1. Click "Edit" button
2. Modify details or links
3. Click "Update Department"

**Deleting a Department:**
- Only delete if no requisitions are linked
- Click "Delete" → Confirm

### Managing Sub-Departments

Sub-departments are organizational units within departments.

**Process is similar to Departments:**
1. Navigate to "Organization" → "Sub-Departments"
2. Create, edit, or delete sub-departments
3. Link to:
   - Parent departments
   - Child divisions

### Managing Divisions

Divisions are the lowest-level organizational units.

**Process is similar to Departments:**
1. Navigate to "Organization" → "Divisions"
2. Create, edit, or delete divisions
3. Link to parent sub-departments

**Organizational Hierarchy:**
```
Department
    ├── Sub-Department 1
    │   ├── Division A
    │   └── Division B
    └── Sub-Department 2
        ├── Division C
        └── Division D
```

**Usage in Requisitions:**
- Users must select Department → Sub-Department → Division when creating requisitions
- This helps track usage by organizational unit
- Used in reports for department activity analysis

---

## 8. Admin: Approving Requisitions

### Viewing Pending Requisitions

1. Navigate to "Administration" → "Requisition Approvals"
2. You'll see all requisitions with filters:
   - **Approval Status**: Pending, Approved, Rejected, All
   - **Clear Status**: Pending (items to issue), Cleared (all issued)
   - **Date Range**
   - **Department/Sub-dept/Division**
   - **User** (who created)

**Statistics:**
- Total requisitions
- Pending approval count
- Approved count
- Rejected count

**Requisition List Columns:**
- Requisition Number
- User (who requested)
- Department/Sub-dept/Division
- Date Created
- Approval Status
- Clear Status
- Total Items
- Actions: View, Approve, Reject, Issue Items

### Reviewing a Requisition

1. Click "View" button on a requisition
2. Review:
   - **Requester Information**: Name, email, department
   - **Requisition Details**: Number, date, notes
   - **Items Requested**:
     - Item code, name, category
     - Requested quantity
     - Preferred location
     - Specifications
   - **Current Status**: Approval and clear status
   - **Issue History** (if any): Previously issued items

### Approving a Requisition

**When to approve:**
- Request is legitimate and authorized
- Items are for valid business use
- User is authorized to request these items

**Steps:**
1. Review requisition details thoroughly
2. Click "Approve" button
3. Confirm approval
4. Requisition status changes to "Approved"
5. Items can now be issued

**What happens after approval:**
- User is notified (if notifications enabled)
- Requisition appears in "Items to Issue" list
- You can proceed to issue items immediately

### Rejecting a Requisition

**When to reject:**
- Unauthorized request
- Items not available or discontinued
- Duplicate request
- Incorrect or incomplete information

**Steps:**
1. Click "Reject" button
2. **Enter Rejection Reason** (required)
   - Be clear and specific
   - User will see this reason
3. Confirm rejection
4. Requisition status changes to "Rejected"
5. All items marked as rejected

**What happens after rejection:**
- User is notified with your reason
- User can create a new requisition if needed
- Rejected requisitions cannot be approved again

---

## 9. Admin: Issuing Items

After approving a requisition, you need to issue (release) items from inventory.

### Understanding Item Issuing

**What is issuing?**
- Physical release of items from warehouse/store
- Updates SAGE 300 ERP inventory
- Creates "Issued Item" records
- Decreases stock quantity and value in SAGE

**SAGE 300 Transaction:**
- Transaction Type: **BothDecrease**
- Decreases: Quantity on hand + Total cost value
- Gets: Unit cost, reference numbers from SAGE

### Viewing Items to Issue

1. Navigate to "Requisition Approvals"
2. Filter by:
   - Approval Status: "Approved"
   - Clear Status: "Pending"
3. These requisitions have items waiting to issue

### Issuing Items - Step by Step

1. **Open Issue Form**
   - Find approved requisition
   - Click "Issue Items" button
   - You'll see the Issue Items page

2. **Review Items to Issue**

   For each requisition item, you'll see:
   - Item code, name, category
   - **Total Requested**: Original quantity requested
   - **Already Issued**: Quantity already issued (if partial)
   - **Remaining to Issue**: How much more to issue
   - **Available Locations**: All SAGE locations with this item

3. **Select Location and Quantity**

   For each item:

   a. **Choose Location:**
      - Dropdown shows all locations with available stock
      - Format: "Location Code - Available: X units"
      - **Available** considers:
        - SAGE stock quantity
        - Minus pending requisitions
        - Real-time calculation

   b. **Enter Issue Quantity:**
      - Cannot exceed **Remaining to Issue**
      - Cannot exceed **Available at Location**
      - You can issue partial quantities

   c. **Add Notes** (Optional):
      - Special handling instructions
      - Condition notes
      - Any other relevant info

   d. **Click "Add Issue":**
      - Issue will be added to the list
      - You can add multiple issues per item (different locations)

4. **Review Issues**
   - Check all items in the issue list
   - Verify locations and quantities
   - Remove any issue if needed

5. **Submit Issues**
   - Click "Submit Issues"
   - **System Process:**
     1. Validates quantities against stock
     2. Posts to SAGE 300 (IC/ICAdjustments - BothDecrease)
     3. Gets unit cost and references from SAGE
     4. Creates Issued Item records
     5. If all items fully issued → Requisition cleared

   - **Success**: You'll see confirmation
   - **Error**: If SAGE posting fails, error message shown

**Important Notes:**
- Always verify location before issuing
- Double-check quantities
- If SAGE posting fails, database is NOT updated (transaction safety)
- You can issue items in multiple batches
- Partial issuing is allowed

### Understanding Partial Issuing

**Scenario:** Requisition has 2 items:
- Item A: 100 units requested
- Item B: 50 units requested

**You can:**
1. Issue 60 units of Item A from Location 1
2. Save and come back later
3. Issue remaining 40 units of Item A from Location 2
4. Issue Item B separately

**Requisition Status:**
- Clear Status remains "Pending" until all items fully issued
- Clear Status changes to "Cleared" when complete

### Troubleshooting Issuing

**"Insufficient stock at location":**
- Check other locations for this item
- Verify pending requisitions aren't blocking stock
- Consider splitting issue across locations

**"SAGE API Error":**
- Check SAGE 300 connection
- Verify item exists in SAGE
- Verify location exists in SAGE
- Contact IT support

**"Cannot exceed remaining quantity":**
- Check how much already issued
- Issue only the remaining amount
- Verify you're not double-issuing

---

## 10. Admin: Processing Returns

When users return items, you must process them by splitting into GRN (good items back to stock) and Scrap (damaged items).

### Understanding Return Processing

**Return Types:**
- **Same Condition**: User returns unused, good items
- **Used**: User returns used or potentially damaged items

**Processing Options:**
- **GRN (Goods Return Note)**:
  - Good quality items
  - Returned to stock in SAGE
  - SAGE Transaction: BothIncrease
  - Increases quantity and cost value

- **Scrap**:
  - Damaged or unusable items
  - Written off (no SAGE posting)
  - Recorded in Scrap Items table
  - Financial write-off

### Viewing Pending Returns

1. Navigate to "Administration" → "Return Approvals"
2. Filter by Status: "Pending"
3. View returns awaiting processing

**Return List Shows:**
- Return date
- User who returned
- Related requisition (if any)
- Number of items
- Status
- Actions: View, Process

### Reviewing a Return

1. Click "View" button on a return
2. Review:
   - **Return Information**: Date, user, requisition
   - **Return Items**:
     - Item code, name
     - Return quantity
     - Return type (Same/Used)
     - Return location
     - User notes
   - **Original Issue Details**: When/where item was issued

### Processing Returns - Step by Step

1. **Open Process Form**
   - Find pending return
   - Click "Process Return" or "Approve Items" button

2. **Review Each Return Item**

   For each item, you'll see:
   - Item details (code, name, category)
   - Return quantity
   - Return type (Same/Used)
   - Return location
   - User notes

3. **Inspect Physical Items**
   - Check actual item condition
   - Verify item code matches
   - Verify quantity matches
   - Assess if usable or damaged

4. **Make Processing Decision**

   For each item:

   a. **Enter Unit Price:**
      - Get price from SAGE
      - Or use last known price
      - Required for financial tracking

   b. **Split Quantity:**

      - **GRN Quantity**: Good items to return to stock
      - **Scrap Quantity**: Damaged items to write off
      - **Rule**: GRN Qty + Scrap Qty = Return Qty

      **Examples:**
      - All good: GRN=100, Scrap=0
      - All damaged: GRN=0, Scrap=100
      - Mixed: GRN=70, Scrap=30

   c. **Select Location** (for GRN):
      - Where to return good items
      - Usually original issue location
      - Can be different if needed

   d. **Modify Item Code** (if needed):
      - If user returned wrong item
      - Change to correct item code

   e. **Add Admin Note** (Optional):
      - Reason for scrap
      - Condition details
      - Any other notes

5. **Submit Processing**
   - Click "Process Returns"
   - **System Process:**

     For each item:

     - **If GRN Quantity > 0:**
       1. Post to SAGE (IC/ICAdjustments - BothIncrease)
       2. Increases stock quantity and value
       3. Gets reference numbers from SAGE
       4. Creates GRN Item record

     - **If Scrap Quantity > 0:**
       1. No SAGE posting
       2. Creates Scrap Item record only
       3. Financial write-off

     - **Update Return Item:**
       - Approve Status:
         - "Approved" = All GRN
         - "Rejected" = All Scrap
         - "Partial" = Both GRN and Scrap
       - Approved by, approved at
       - Admin note

     - **When all items processed:**
       - Return Status → "Cleared"

   - **Success**: You'll see confirmation
   - **Error**: If SAGE posting fails, error shown

### Processing Decision Guidelines

**Return Type: SAME**
- User claims item unused
- **Check**: Packaging, seals, condition
- **If perfect**: All GRN
- **If any damage**: Partial or all Scrap

**Return Type: USED**
- User used the item
- **Check**: Functionality, condition, cleanliness
- **If still usable**: GRN
- **If worn/damaged**: Scrap
- **If mixed batch**: Split GRN/Scrap

**Financial Implications:**
- **GRN**: Asset returned, value recovered
- **Scrap**: Asset lost, value written off
- Be fair but conservative with GRN decisions

### Viewing Processed Returns

1. Click "View" on any return
2. After processing, you'll see:
   - **Return Items** with approval status
   - **GRN Items**:
     - GRN quantity, location
     - Unit price, total price
     - SAGE reference numbers
   - **Scrap Items**:
     - Scrap quantity
     - Unit price, total value written off
   - **Admin Notes**

---

## 11. Admin: Managing Purchase Orders

Purchase Orders (PO) are automatically created when requisitioned items exceed available stock.

### Understanding Purchase Orders

**When POs are created:**
- User requisitions 100 units
- Only 60 units available in stock
- System creates:
  - Requisition item: 100 units (60 can be issued now)
  - PO item: 40 units (needs purchasing)

**PO Lifecycle:**
1. **Created**: Automatically when requisitioning out-of-stock items
2. **Pending**: Waiting for physical purchase/receipt
3. **Cleared**: Admin marks as received
4. **Issued**: Admin issues items to user (normal issue process)

### Viewing Purchase Orders

1. Navigate to "Administration" → "Purchase Orders"

2. **Group By Options:**
   - **By Requisition**: All PO items per requisition
   - **By Item**: Total quantity needed per item across all requisitions

3. **Filter Options:**
   - Status: Pending, Cleared
   - Date Range
   - Item Code
   - Requisition Number

**PO List Columns:**
- Item code, name, category
- Location
- Quantity needed
- Related requisition(s)
- Status
- Created date
- Actions: View, Clear

### Viewing PO Details

1. Click "View" on any PO item
2. You'll see:
   - Item details
   - Quantity needed
   - Related requisition information
   - Requester details
   - Date created
   - Current status

### Clearing Purchase Orders

**When to clear:**
- Items have been physically ordered from supplier
- Items have been received in warehouse
- Items have been entered into SAGE 300
- Ready to issue to requester

**Steps to Clear:**

1. **Clear Single PO:**
   - Find PO item in list
   - Click "Clear" button
   - Confirm clearance
   - Status → "Cleared"

2. **Bulk Clear by Requisition:**
   - Useful when all items for a requisition arrive together
   - Select requisition from dropdown
   - Click "Clear Requisition POs"
   - All PO items for that requisition → "Cleared"

3. **Bulk Clear by Item:**
   - Useful when bulk ordering same item for multiple requisitions
   - Select item code from dropdown
   - Click "Clear Item POs"
   - All PO items with that code → "Cleared"

4. **Clear All Pending:**
   - Use with caution
   - Clears ALL pending PO items
   - Confirm you've received everything

**After Clearing:**
- PO items marked as cleared
- Items still need to be entered in SAGE
- Then issue to users via normal issue process

### PO Workflow Example

**Scenario:**
1. User requests 100 units of Item A
2. Only 60 in stock
3. System creates PO for 40 units

**Admin Actions:**
1. **Approve requisition**
2. **Issue 60 units** immediately (from stock)
3. **Order 40 units** from supplier
4. **Wait for delivery**
5. **Receive in warehouse** → Enter in SAGE
6. **Clear PO** (mark as received)
7. **Issue remaining 40 units** to user
8. Requisition fully cleared

---

## 12. Reports & Analytics

The system provides comprehensive reports for tracking requisitions, returns, and inventory usage.

### Accessing Reports

Navigate to "Reports" in the sidebar (Admin/Manager only)

### Available Reports

#### 1. Reports Dashboard

**Path**: Reports → Dashboard

**What it shows:**
- Quick overview of all report categories
- Summary statistics
- Links to detailed reports

**Includes:**
- Total requisitions this month
- Total returns this month
- Total value issued
- Top requested items
- Department activity summary

#### 2. Requisition Summary Report

**Path**: Reports → Requisition Summary

**What it shows:**
- All requisitions with full details
- Filter by date, status, department, user
- Statistics: total, pending, approved, rejected

**Columns:**
- Requisition number
- User
- Department/Sub-dept/Division
- Date
- Status (approval and clear)
- Total items
- Notes

**Use Cases:**
- Monthly requisition review
- Department usage tracking
- User activity monitoring
- Approval rate analysis

**Export:** Excel

#### 3. Item Requisition Report

**Path**: Reports → Item Requisition

**What it shows:**
- All requisitioned items across all requisitions
- Item-level detail
- Top 10 most requested items

**Columns:**
- Item code, name, category
- Requisition number
- Requested quantity
- Location
- Date
- User
- Department

**Filters:**
- Date range
- Item code/name
- Department

**Use Cases:**
- Item demand analysis
- Inventory planning
- Popular items identification
- Stock forecasting

**Export:** Excel

#### 4. Issued Items Report

**Path**: Reports → Issued Items

**What it shows:**
- All items that have been issued
- Financial values (unit price, total)
- Issue history with SAGE references

**Columns:**
- Item code, name, category
- Requisition number
- Issued quantity
- Unit price
- Total price
- Location issued from
- Issued by
- Issue date
- SAGE references

**Statistics:**
- Total issues count
- Total quantity issued
- Total value issued

**Filters:**
- Date range
- Item code
- Issued by (admin)
- Location

**Use Cases:**
- Financial tracking
- Cost analysis by department
- Inventory movement tracking
- Audit trail

**Export:** Excel

#### 5. Purchase Order Report

**Path**: Reports → Purchase Orders

**What it shows:**
- All PO items (out-of-stock requisitions)
- Pending and cleared POs
- Grouped by requisition or item

**Columns:**
- Item code, name, category
- Quantity
- Requisition number
- Location
- Status
- Created date
- Cleared date

**Statistics:**
- Total PO items
- Pending count
- Cleared count
- Total quantity on PO

**Filters:**
- Status (pending/cleared)
- Date range
- Item code
- Requisition number

**Use Cases:**
- Purchasing planning
- Stock replenishment tracking
- Backorder monitoring
- Supplier order preparation

**Export:** Excel

#### 6. Returns Summary Report

**Path**: Reports → Returns Summary

**What it shows:**
- All returns with status
- Return counts and trends

**Columns:**
- Return date
- User
- Related requisition
- Items count
- Status
- Processed date

**Statistics:**
- Total returns
- Pending returns
- Cleared returns

**Filters:**
- Date range
- Status
- User
- Requisition number

**Use Cases:**
- Return rate analysis
- Quality issue identification
- User return patterns
- Process efficiency tracking

**Export:** Excel

#### 7. GRN (Goods Return Note) Report

**Path**: Reports → GRN Report

**What it shows:**
- All items successfully returned to stock
- Financial recovery from returns

**Columns:**
- Item code, name, category
- Return date
- GRN quantity
- Unit price
- Total value
- Location
- SAGE references
- Processed by

**Statistics:**
- Total GRN items
- Total quantity returned to stock
- Total value recovered

**Top 10:** Most returned items

**Filters:**
- Date range
- Item code
- Location

**Use Cases:**
- Stock replenishment tracking
- Financial recovery analysis
- Return quality assessment
- Inventory reconciliation

**Export:** Excel

#### 8. Scrap Report

**Path**: Reports → Scrap Report

**What it shows:**
- All items written off as damaged/unusable
- Financial losses from scrapping

**Columns:**
- Item code, name, category
- Return date
- Scrap quantity
- Unit price
- Total value (loss)
- Location
- Processed by

**Statistics:**
- Total scrap items
- Total quantity scrapped
- Total value lost

**Top 10:** Most scrapped items

**Filters:**
- Date range
- Item code
- Location

**Use Cases:**
- Loss analysis
- Quality issue identification
- Supplier quality tracking
- Waste reduction planning

**Export:** Excel

#### 9. Department Activity Report

**Path**: Reports → Department Activity

**What it shows:**
- Requisition activity by department
- Usage patterns by organizational unit

**Columns:**
- Department name
- Sub-department
- Division
- Total requisitions
- Pending
- Approved
- Rejected
- Total items requested

**Filters:**
- Date range
- Department
- Sub-department
- Division

**Use Cases:**
- Department usage comparison
- Budget allocation planning
- Activity trends by unit
- Resource distribution analysis

**Export:** Excel

#### 10. User Activity Report

**Path**: Reports → User Activity

**What it shows:**
- Requisition and return activity by user
- User behavior patterns

**Columns:**
- User name, email
- Total requisitions
- Pending requisitions
- Approved requisitions
- Total returns
- Pending returns

**Filters:**
- Date range
- User
- Department

**Use Cases:**
- User usage tracking
- Heavy users identification
- Training needs assessment
- Compliance monitoring

**Export:** Excel

#### 11. Monthly Summary Report

**Path**: Reports → Monthly Summary

**What it shows:**
- Year-wise summary (12 months)
- Trends over time

**Metrics by Month:**
- Total requisitions
- Total returns
- Items issued
- GRN items
- Value issued
- Value recovered

**Display:**
- Table format with months as columns
- Trend graphs (if available)
- Year-over-year comparison

**Filters:**
- Year

**Use Cases:**
- Annual reporting
- Trend analysis
- Budget planning
- Performance metrics

**Export:** Excel

### Exporting Reports

All reports can be exported to Excel:

1. Apply your filters
2. Click "Export to Excel" button
3. File will download with:
   - Report name in filename
   - Date range in filename
   - All filtered data
   - Formatted columns
   - Totals/summaries

**Excel File Naming:**
- Format: `ReportName_StartDate_EndDate.xlsx`
- Example: `RequisitionSummary_2024-01-01_2024-01-31.xlsx`

### Report Best Practices

**Filters:**
- Always use date ranges for large datasets
- Apply specific filters for focused analysis
- Export filtered data for sharing

**Frequency:**
- Daily: Pending approvals, items to issue
- Weekly: Department activity, user activity
- Monthly: All financial reports, monthly summary
- Quarterly: Trend analysis, budget reviews
- Yearly: Annual summary, year-over-year comparison

**Data Analysis:**
- Compare month-over-month trends
- Identify top items for stock planning
- Monitor scrap rates for quality issues
- Track department usage for budgets
- Review user activity for compliance

---

## 13. Troubleshooting & FAQs

### Common Issues

#### Cannot Login

**Problem:** "Invalid credentials" error

**Solutions:**
1. Verify email and password are correct
2. Check Caps Lock is off
3. Try password reset
4. Contact admin to verify account is active

---

#### Cannot See Requisitions/Returns Menu

**Problem:** Menu items missing from sidebar

**Solutions:**
1. Verify you have the required permissions
2. Ask admin to check your role and permissions
3. Log out and log back in
4. Clear browser cache

---

#### Item Not Found When Creating Requisition

**Problem:** Cannot find item in search

**Solutions:**
1. Verify item exists in SAGE 300
2. Try searching by item code instead of name
3. Check for typos in search
4. Contact admin if item should exist

---

#### "Insufficient Stock" Error When Issuing

**Problem:** Cannot issue requested quantity

**Solutions:**
1. Check available quantity at location
2. Verify other pending requisitions aren't blocking stock
3. Issue from different location
4. Issue partial quantity now, rest later
5. Create purchase order for additional stock

---

#### SAGE 300 Connection Error

**Problem:** "SAGE API Error" messages

**Solutions:**
1. Retry the operation (temporary network issue)
2. Check with IT if SAGE server is running
3. Verify SAGE credentials in system settings
4. Contact IT support for SAGE connectivity issues

---

#### Return Quantity Exceeds Issued

**Problem:** "Cannot return more than issued quantity"

**Solutions:**
1. Check how much was actually issued
2. Check if you already returned some quantity
3. Verify you're selecting the correct issued item
4. Enter only the remaining returnable quantity

---

#### Cannot Delete Requisition/Return

**Problem:** Delete button is disabled or gives error

**Solutions:**
1. Can only delete items with "Pending" status
2. Cannot delete approved/rejected/cleared items
3. Contact admin if you need to cancel an approved item

---

### Frequently Asked Questions

#### General Questions

**Q: How long does approval take?**
A: Depends on admin availability. Typically within 1-2 business days. Contact your admin for urgent requests.

**Q: Can I edit a requisition after submitting?**
A: No. If it's still pending, you can delete and create a new one. If approved, contact admin.

**Q: What happens if I request more than available stock?**
A: The system automatically creates a Purchase Order for the excess quantity. You'll get available stock immediately, and the rest when PO is cleared.

**Q: Can I return partial quantities?**
A: Yes. You don't have to return all issued items at once. Return what you need to, keep the rest.

---

#### Requisition Questions

**Q: Why was my requisition rejected?**
A: Check the rejection reason provided by admin. Common reasons: unauthorized request, item not available, duplicate, or incorrect information.

**Q: How do I know when my items are issued?**
A: Check your requisition status. When "Clear Status" shows "Cleared", all items have been issued. You'll also see issued items in the requisition details.

**Q: Can I have multiple pending requisitions?**
A: Yes, there's no limit. But try to consolidate requests when possible.

**Q: What if I need an item urgently?**
A: Add a note in the requisition explaining urgency. Or contact admin directly after submitting.

---

#### Return Questions

**Q: Do I need to return items to the same location?**
A: Not necessarily. You can specify the return location. Usually it's the original issue location, but admin can change it during processing.

**Q: What's the difference between "Same" and "Used" return type?**
A: "Same" = unused, original condition. "Used" = has been used or may be damaged. This helps admin decide on GRN vs Scrap.

**Q: What happens to returned items?**
A: Admin inspects and decides:
- Good items: Returned to stock (GRN)
- Damaged items: Written off (Scrap)
- Mixed: Partial GRN, partial Scrap

**Q: How long does return processing take?**
A: Typically 2-3 business days. Admin needs to physically inspect items.

---

#### Admin Questions

**Q: Should I approve requisitions immediately?**
A: Review each requisition for:
- Legitimacy of request
- Authorization of user
- Availability of items
- Business need
Approve if all criteria met.

**Q: Can I partial issue requisitions?**
A: Yes. Issue what's available now, come back later for the rest. Requisition stays "Pending" until all items issued.

**Q: What if SAGE posting fails during issue/GRN?**
A: The database is NOT updated (transaction safety). Fix the SAGE issue and retry. Common causes:
- SAGE connection down
- Item/Location doesn't exist in SAGE
- Insufficient permissions
- SAGE batch locked

**Q: How do I decide GRN vs Scrap?**
A: Physically inspect returned items:
- Usable, good condition → GRN
- Damaged, unusable → Scrap
- When in doubt, prefer Scrap (conservative approach)
- Document decision in admin notes

---

#### Permission Questions

**Q: Why can't a user approve their own requisition?**
A: Separation of duties. Users request, admins/managers approve. This prevents unauthorized self-approval.

**Q: Can I give a user partial admin access?**
A: Yes! Use the "Manage Permissions" feature to assign specific permissions beyond their role.

**Q: What happens when I remove a permission from a user?**
A: They immediately lose access to that function. Sidebar menu items hide, route access blocked.

**Q: What's the difference between role permissions and direct permissions?**
A:
- Role permissions: Inherited from assigned role(s)
- Direct permissions: User-specific overrides
- User gets merged set of both

---

### Getting Help

**Technical Issues:**
- Contact IT Support at: [support email]
- Include error message screenshots
- Describe steps that led to error

**Business Questions:**
- Contact your department admin/manager
- Review this User Guide
- Check requisition/return status in system

**Feature Requests:**
- Submit to: [feedback email]
- Describe desired functionality
- Explain business need

---

**System Support Hours:**
- Monday - Friday: 8:00 AM - 5:00 PM
- Closed weekends and public holidays
- Emergency hotline: [phone number]

---

**End of User Guide**

For technical details and system architecture, refer to the TECHNICAL_DOCUMENTATION.md file.
