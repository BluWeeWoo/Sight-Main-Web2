<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #F9FFFB;
            color: #1f2937;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 60px 60px;
        }

        .tabs {
            position: relative;
            display: flex;
            justify-content: left;
            width: 100%;
            gap: 5px;
            margin-bottom: 30px;
            background: white;
            padding: 10px;
            border-radius: 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .tab-indicator {
            position: absolute;
            background: #527267;
            border-radius: 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 0;
        }

        .tab {
            position: relative;
            z-index: 1;
            padding: 10px 24px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: #9ca3af;
            border: none;
            background: transparent;
            border-radius: 24px;
            transition: color 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tab.active {
            color: white;
        }

        .tab:hover {
            color: #1f2937;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 30px 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
        }

        .professionals-panel {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
        }

        .toolbar {
            display: flex;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            padding: 10px 16px;
            border: 1px solid #66736d;
            border-radius: 28px;
            font-size: 14px;
            outline: none;
        }

        .search-box:focus {
            border-color: #295c4a;
        }

        .add-button {
            background: #81A398;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            transition: background 0.2s;
            margin-left: auto;
        
        }

        .add-button:hover {
            background: #16a34a;
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 18px;
            border: none;
            border-radius: 16px;
           
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
            background: #f7faf9;
            color: #527267;
            border: 1px solid #527267;
        }

        .filter-btn.active {
            background: #527267;
            color: white;
        }

        .filter-btn:hover {
            opacity: 0.8;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        th {
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        tbody tr:hover {
            background: #fafbfc;
        }

        .professional-name {
            font-weight: 700;
            color: #1f2937;
            font-size: 15px;
        }

        .professional-specialty {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 2px;
        }

        .professional-email {
            color: #6b7280;
            font-size: 13px;
        }

        .professional-phone {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 2px;
        }

        .clinic-info {
            font-size: 13px;
        }

        .clinic-name {
            color: #1f2937;
            font-weight: 500;
        }

        .clinic-license {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 2px;
        }

        .last-active-time {
            color: #1f2937;
            font-weight: 500;
            font-size: 13px;
        }

        .joined-date {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 2px;
        }

        .patient-badge {
            background: #527267;
            border-radius: 15px;
            padding: 8px 15px;
            font-weight: 600;
            font-size: 13px;
            color: #fff;
        }

        .status-badge {
            border-radius: 15px;
            padding: 8px 11px;
            font-size: 13px;
            display: inline-block;
        }

        .status-active {
            background: #E5FFF6;
            color: #7BC680;
            border: 1px solid #7BC680;
        }

        .status-inactive {
            background: #e5e7eb;
            color: #6b7280;
        }

        .status-suspended {
            background: #fee2e2;
            color: #dc2626;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        .action-btn {
            background: none;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            transition: color 0.2s;
        }

        .action-btn.edit {
            color: #295c4a;
        }

        .action-btn.edit:hover {
            color: #1f2937;
        }

        .action-btn.delete {
            color: #ef4444;
        }

        .action-btn.delete:hover {
            color: #dc2626;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 50;
        }

        .modal-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal {
            background: white;
            border-radius: 12px;
            padding: 24px;
            min-width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 32px rgba(0,0,0,0.15);
        }

        .modal-header {
            margin-bottom: 16px;
        }

        .modal-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
        }

        .modal-body {
            margin-bottom: 16px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }

        .form-grid.edit {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 9999px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
            background: #ffffff;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #295c4a;
        }

        .modal-footer {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .modal-footer button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #22c55e;
            color: white;
        }

        .btn-primary:hover {
            background: #16a34a;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .btn-save {
            background: #295c4a;
            color: white;
        }

        .btn-save:hover {
            background: #1f3a34;
        }

        .settings-form {
            background: transparent;
            padding: 0;
        }

        .form-section {
            background: #FCFFFD;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            margin-bottom: 24px;
            display: flex;
            gap: 20px;
        }

        .form-section::before {
            content: '';
            min-width: 50px;
            width: 50px;
            height: 50px;
            background: #527267;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .form-section h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #1f2937;
        }

        .form-section > div {
            flex: 1;
        }

        .section-subtitle {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: #374151;
            margin-bottom: 8px;
        }

        .checkbox-label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            margin: 0;
            margin-bottom: 4px;
        }

        .checkbox-description {
            font-size: 12px;
            color: #9ca3af;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 9999px;
            font-size: 13px;
            outline: none;
            transition: all 0.2s;
        }

        input:focus {
            border-color: #295c4a;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            background: #F0FAF8;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .checkbox-info {
            flex: 1;
        }

        input[type="checkbox"] {
            display: none;
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 28px;
            background: #d1d5db;
            border-radius: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: left 0.3s;
        }

        input[type="checkbox"]:checked + .toggle-switch {
            background: #22c55e;
        }

        input[type="checkbox"]:checked + .toggle-switch::after {
            left: 24px;
        }

        .admin-list {
            margin-top: 15px;
        }

        .admin-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .admin-item:last-child {
            border-bottom: none;
        }

        .admin-email {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .admin-status {
            background: #dcfce7;
            color: #166534;
            padding: 6px 14px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
        }

        .admin-action {
            color: #ef4444;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            line-height: 1;
            background: none;
            border: none;
            padding: 0;
            transition: opacity 0.2s;
        }

        .admin-action:hover {
            opacity: 0.7;
        }

        .save-button {
            background: #527267;
            color: white;
            padding: 12px 32px;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-top: 20px;
            transition: background 0.2s;
            display: block;
            margin-left: auto;
            margin-right: 0;
        }

        .save-button:hover {
            background: #3d5a55;
        }

        .add-admin-section {
            text-align: right;
            margin-bottom: 20px;
        }

        .add-admin-btn {
            background: #527267;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .add-admin-btn:hover {
            background: #3d5a55;
        }

        @media (max-width: 1024px) {
            .form-grid,
            .form-row {
                grid-template-columns: 1fr 1fr;
            }

            .modal {
                min-width: 95%;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: unset;
            }

            .form-grid,
            .form-row {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            td, th {
                padding: 8px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 6px;
            }

            .modal {
                min-width: 95%;
                padding: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="tabs">
            <div id="tab-indicator" class="tab-indicator"></div>
            <div class="tab active" onclick="showSection(this, 'professionals')">Professionals</div>
            <div class="tab" onclick="showSection(this, 'settings')">Settings</div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0; margin-left: auto;">
                @csrf
                <button type="submit" class="tab" style="color: #ef4444; cursor: pointer;">Logout</button>
            </form>
        </div>

        <!-- Professionals Section -->
        <div id="professionals" class="content-section active" x-data="professionalsManager()">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card" style="background-color: #4B6059;">
                    <div class="stat-label" style="color: rgba(255, 255, 255, 0.8);">Total Professionals</div>
                    <div class="stat-value" style="color: white;" x-text="professionals.length"></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active</div>
                    <div class="stat-value" x-text="professionals.filter(p => p.status.toLowerCase() === 'active').length"></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Patients</div>
                    <div class="stat-value" x-text="professionals.reduce((sum, p) => sum + p.patients, 0)"></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Suspended</div>
                    <div class="stat-value" x-text="professionals.filter(p => p.status.toLowerCase() === 'suspended').length"></div>
                </div>
            </div>

            <div class="professionals-panel">
                <!-- Toolbar -->
                <div class="toolbar">
                    <input 
                        type="text" 
                        class="search-box" 
                        placeholder="Search by name, email, or clinic..."
                        x-model="searchTerm"
                        @input="filterProfessionals()"
                    >
                    <div class="filter-tabs">
                        <template x-for="status in ['All', 'Active', 'Inactive', 'Suspended']" :key="status">
                            <button 
                                class="filter-btn" 
                                :class="{ active: filterStatus === status }"
                                @click="filterStatus = status; filterProfessionals()"
                                x-text="status"
                            ></button>
                        </template>
                    </div>
                    <button class="add-button" @click="showAddModal = true">+ Add Professionals</button>
                </div>

                <!-- Add Modal -->
                <div class="modal-overlay" :class="{ active: showAddModal }">
                    <div class="modal">
                        <div class="modal-header">
                            <h3>Add New Professional</h3>
                        </div>
                        <div class="modal-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input 
                                        type="text" 
                                        placeholder="Name"
                                        x-model="newProfessional.name"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input 
                                        type="email"
                                        placeholder="Email"
                                        x-model="newProfessional.email"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input 
                                        type="text"
                                        placeholder="Phone"
                                        x-model="newProfessional.phone"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>Specialty</label>
                                    <input 
                                        type="text"
                                        placeholder="Specialty"
                                        x-model="newProfessional.specialty"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>Clinic</label>
                                    <input 
                                        type="text"
                                        placeholder="Clinic"
                                        x-model="newProfessional.clinic"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>License</label>
                                    <input 
                                        type="text"
                                        placeholder="License Number"
                                        x-model="newProfessional.license_number"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <input 
                                        type="text"
                                        placeholder="Location"
                                        x-model="newProfessional.location"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn-primary" @click="addProfessional()">Add</button>
                            <button class="btn-secondary" @click="showAddModal = false">Cancel</button>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal-overlay" :class="{ active: editingProfessional !== null }">
                    <div class="modal" x-show="editingProfessional !== null">
                        <div class="modal-header">
                            <h3>Edit Professional</h3>
                        </div>
                        <div class="modal-body" x-show="editingProfessional !== null">
                            <div class="form-grid edit">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input 
                                        type="text"
                                        x-model="editingProfessional.name"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input 
                                        type="email"
                                        x-model="editingProfessional.email"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select x-model="editingProfessional.status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" x-model="editingProfessional.phone">
                                </div>
                                <div class="form-group">
                                    <label>Clinic</label>
                                    <input type="text" x-model="editingProfessional.clinic">
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" x-model="editingProfessional.location">
                                </div>
                                <div class="form-group">
                                    <label>Specialty</label>
                                    <input type="text" x-model="editingProfessional.specialty">
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label>License Number</label>
                                    <input type="text" x-model="editingProfessional.license_number">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn-save" @click="saveEdit()">Save</button>
                            <button class="btn-secondary" @click="editingProfessional = null">Cancel</button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Professionals</th>
                                <th>Contact</th>
                                <th>Clinic</th>
                                <th>Patients</th>
                                <th>Status</th>
                                <th>Last Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="pro in filteredProfessionals" :key="pro.id">
                                <tr>
                                    <td>
                                        <div class="professional-name" x-text="pro.name"></div>
                                        <div class="professional-specialty" x-text="pro.specialty"></div>
                                    </td>
                                    <td>
                                        <div class="professional-email" x-text="pro.email"></div>
                                        <div class="professional-phone" x-text="pro.phone"></div>
                                    </td>
                                    <td>
                                        <div class="clinic-info">
                                            <div class="clinic-name" x-text="pro.clinic"></div>
                                            <div class="clinic-license" x-text="'License: ' + pro.license_number"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="patient-badge" x-text="pro.patients"></span>
                                    </td>
                                    <td>
                                        <span 
                                            class="status-badge"
                                            :class="'status-' + pro.status.toLowerCase()"
                                            x-text="capitalizeStatus(pro.status)"
                                        ></span>
                                    </td>
                                    <td>
                                        <div class="last-active-time" x-text="pro.last_active"></div>
                                        <div class="joined-date" x-text="'Joined: ' + pro.joined_date"></div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button 
                                                class="action-btn edit"
                                                @click="editProfessional(pro)"
                                            >Edit</button>
                                            <button 
                                                class="action-btn delete"
                                                @click="deleteProfessional(pro.id)"
                                            >Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Settings Section -->
        <div id="settings" class="content-section">
            @if(session('success'))
                <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="settings-form" action="{{ url('/admin/settings') }}" method="POST">
                @csrf
                <div class="form-section">
                    <div>
                        <h3>Account Settings</h3>
                        <p class="section-subtitle">Manage your account information</p>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" value="{{ $admin->name }}" placeholder="Enter full name" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" value="{{ $admin->email }}" placeholder="Enter email" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" value="{{ $admin->phone ?? '' }}" placeholder="Enter phone number">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div>
                        <h3>Change Password</h3>
                        <p class="section-subtitle">Update your password regularly</p>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" placeholder="Enter current password">
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="new_password_confirmation" placeholder="Confirm password">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div>
                        <h3>Security Settings</h3>
                        <p class="section-subtitle">Manage security and authentication</p>
                        <div class="checkbox-wrapper">
                            <div class="checkbox-info">
                                <label class="checkbox-label">Two-Factor Authentication</label>
                                <div class="checkbox-description">Add an extra layer of security</div>
                            </div>
                            <input type="checkbox" id="twofa">
                            <label for="twofa" class="toggle-switch"></label>
                        </div>
                        <div class="checkbox-wrapper">
                            <div class="checkbox-info">
                                <label class="checkbox-label">Login Alerts</label>
                                <div class="checkbox-description">Get notified of new login attempts</div>
                            </div>
                            <input type="checkbox" id="login-alerts">
                            <label for="login-alerts" class="toggle-switch"></label>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div>
                        <h3>System Settings</h3>
                        <p class="section-subtitle">Configure system-wide options</p>
                        <div class="checkbox-wrapper">
                            <div class="checkbox-info">
                                <label class="checkbox-label">Two-Factor Authentication</label>
                                <div class="checkbox-description">Add an extra layer of security</div>
                            </div>
                            <input type="checkbox" id="system-twofa">
                            <label for="system-twofa" class="toggle-switch"></label>
                        </div>
                        <div class="checkbox-wrapper">
                            <div class="checkbox-info">
                                <label class="checkbox-label">Login Alerts</label>
                                <div class="checkbox-description">Get notified of new login attempts</div>
                            </div>
                            <input type="checkbox" id="system-login-alerts">
                            <label for="system-login-alerts" class="toggle-switch"></label>
                        </div>
                        <div class="form-row" style="margin-top: 20px;">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" placeholder="Enter current password">
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" placeholder="Confirm password">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div>
                        <h3>Other Administrators</h3>
                        <p class="section-subtitle">Manage admin accounts</p>
                        <div class="add-admin-section">
                            <button type="button" class="add-admin-btn">+ Add Admin</button>
                        </div>
                        <div class="admin-list">
                            @forelse($otherAdmins as $other)
                            <div class="admin-item">
                                <div>
                                    <div class="professional-name">{{ $other['name'] }}</div>
                                    <div class="admin-email">{{ $other['email'] }}</div>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <span class="admin-status">{{ $other['status'] }}</span>
                                    <span class="admin-action" onclick="if(confirm('Remove this admin?')) this.parentElement.parentElement.remove()">🗑</span>
                                </div>
                            </div>
                            @empty
                            <p style="text-align: center; color: #9ca3af; padding: 20px;">No other administrators</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <button type="submit" class="save-button">Save All Settings</button>
            </form>
        </div>
    </div>

    <script>
        function moveIndicator(element) {
            const indicator = document.getElementById('tab-indicator');
            if (!indicator || !element) return;
            
            indicator.style.width = element.offsetWidth + 'px';
            indicator.style.left = element.offsetLeft + 'px';
            indicator.style.height = element.offsetHeight + 'px';
            indicator.style.top = element.offsetTop + 'px';
        }

        function showSection(element, sectionId) {
            document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');
            element.classList.add('active');
            
            moveIndicator(element);
        }

        window.addEventListener('load', () => moveIndicator(document.querySelector('.tab.active')));
        window.addEventListener('resize', () => moveIndicator(document.querySelector('.tab.active')));

        function professionalsManager() {
            return {
                professionals: @json($professionals),
                csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                filteredProfessionals: @json($professionals),
                searchTerm: '',
                filterStatus: 'All',
                showAddModal: false,
                editingProfessional: null,
                newProfessional: {
                    name: '',
                    email: '',
                    phone: '',
                    specialty: '',
                    clinic: '',
                    license_number: '',
                    location: '',
                    password: '',
                    password_confirmation: ''
                },

                filterProfessionals() {
                    console.log('Filter called. filterStatus:', this.filterStatus);
                    console.log('Professionals:', this.professionals);
                    
                    this.filteredProfessionals = this.professionals.filter((pro) => {
                        const proStatus = (pro.status || 'active').toLowerCase();
                        const matchesSearch =
                            (pro.name || '').toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            (pro.email || '').toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            (pro.clinic || '').toLowerCase().includes(this.searchTerm.toLowerCase());
                        
                        const matchesFilter = 
                            this.filterStatus === 'All' || 
                            proStatus === this.filterStatus.toLowerCase();
                        
                        console.log(`Pro: ${pro.name}, Status: ${proStatus}, FilterStatus: ${this.filterStatus}, Matches: ${matchesFilter}`);
                        
                        return matchesSearch && matchesFilter;
                    });
                    
                    console.log('Filtered result count:', this.filteredProfessionals.length);
                },

                capitalizeStatus(status) {
                    if (!status) return 'Active';
                    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
                },

                async addProfessional() {
                    if (!this.newProfessional.name || !this.newProfessional.email || !this.newProfessional.clinic) {
                        alert('Please fill in all required fields');
                        return;
                    }

                    try {
                        const response = await fetch('/admin/professionals', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify(this.newProfessional)
                        });
                        
                        const data = await response.json();
                        if (data.success) {
                            // Use the server-returned object (it has the real ID and defaults)
                            this.professionals.push({
                                ...data.professional,
                                patients: 0,
                                last_active: 'Just now',
                                status: data.professional.status || 'active',
                                joined_date: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
                            });
                            this.filterProfessionals();
                            this.resetNewProfessional();
                            this.showAddModal = false;
                        } else {
                            alert(data.message || 'Error adding professional');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('A server error occurred');
                    }
                },

                resetNewProfessional() {
                    this.newProfessional = { 
                        name: '', email: '', phone: '', specialty: '', 
                        clinic: '', license_number: '', location: '', 
                        password: '', password_confirmation: '' 
                    };
                },

                editProfessional(pro) {
                    // Ensure status is lowercase for the select binding
                    this.editingProfessional = { ...pro, status: pro.status.toLowerCase() };
                },

                async saveEdit() {
                    if (this.editingProfessional) {
                        try {
                            const response = await fetch(`/admin/professionals/${this.editingProfessional.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken
                                },
                                body: JSON.stringify(this.editingProfessional)
                            });
                            
                            if (!response.ok) throw new Error('Update failed');
                        } catch (error) {
                            alert('Failed to save changes to the server');
                            return;
                        }

                        const index = this.professionals.findIndex(p => p.id === this.editingProfessional.id);
                        if (index !== -1) {
                            this.professionals[index] = this.editingProfessional;
                        }
                        this.filterProfessionals();
                        this.editingProfessional = null;
                    }
                },

                async deleteProfessional(id) {
                    if (confirm('Are you sure you want to delete this professional?')) {
                        try {
                            const response = await fetch(`/admin/professionals/${id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': this.csrfToken }
                            });
                            
                            if (!response.ok) throw new Error('Delete failed');
                            
                            this.professionals = this.professionals.filter(p => p.id !== id);
                        } catch (error) {
                            alert('Failed to delete from server');
                        }
                        
                        this.filterProfessionals();
                    }
                }
            };
        }
    </script>
</body>
</html>
