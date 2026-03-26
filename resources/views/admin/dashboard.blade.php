<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lumi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .header {
            background: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .header h1 {
            font-size: 20px;
            font-weight: 600;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .tabs {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab {
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: #999;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            margin-bottom: -2px;
        }

        .tab.active {
            color: #2c3e50;
            border-bottom-color: #2c3e50;
        }

        .tab:hover {
            color: #2c3e50;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stat-label {
            font-size: 13px;
            color: #999;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 20px;
        }

        .search-box {
            flex: 1;
            max-width: 500px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .add-button {
            background: #1abc9c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .add-button:hover {
            background: #16a085;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-btn {
            padding: 8px 15px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .filter-btn:hover {
            background: #1a252f;
        }

        .filter-btn.inactive {
            background: #e0e0e0;
            color: #666;
        }

        .filter-btn.inactive:hover {
            background: #d0d0d0;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #666;
            border-bottom: 1px solid #e0e0e0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        .doctor-name {
            font-weight: 600;
            color: #333;
        }

        .contact-email {
            color: #999;
            font-size: 13px;
        }

        .contact-location {
            color: #999;
            font-size: 12px;
        }

        .clinic-name {
            color: #2c3e50;
            font-weight: 500;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .status-suspended {
            background: #f8d7da;
            color: #721c24;
        }

        .action-links {
            display: flex;
            gap: 15px;
        }

        .action-link {
            color: #2c3e50;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .action-link.delete {
            color: #e74c3c;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .settings-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
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
            color: #333;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #2c3e50;
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-label {
            font-weight: 500;
            font-size: 13px;
            margin: 0;
        }

        .checkbox-description {
            font-size: 12px;
            color: #999;
        }

        .admin-list {
            margin-top: 15px;
        }

        .admin-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .admin-email {
            font-size: 13px;
            color: #999;
        }

        .admin-status {
            background: #1abc9c;
            color: white;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .admin-action {
            color: #e74c3c;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .save-button {
            background: #2c3e50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-top: 20px;
        }

        .save-button:hover {
            background: #1a252f;
        }

        .add-admin-section {
            text-align: right;
            margin-bottom: 20px;
        }

        .add-admin-btn {
            background: #1abc9c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-tabs {
                flex-wrap: wrap;
            }

            table {
                font-size: 12px;
            }

            td, th {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="showSection('professionals')">Professionals</div>
            <div class="tab" onclick="showSection('settings')">Admin Settings</div>
        </div>

        <!-- Professionals Section -->
        <div id="professionals" class="content-section active">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Professionals</div>
                    <div class="stat-value">5</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active</div>
                    <div class="stat-value">3</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Patients</div>
                    <div class="stat-value">878</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Suspended</div>
                    <div class="stat-value">1</div>
                </div>
            </div>

            <div class="toolbar">
                <input type="text" class="search-box" placeholder="Search by name, email, or clinic...">
                <button class="add-button">+ Add Professionals</button>
            </div>

            <div class="filter-tabs">
                <button class="filter-btn" onclick="filterTable('all')">All</button>
                <button class="filter-btn inactive" onclick="filterTable('active')">Active</button>
                <button class="filter-btn inactive" onclick="filterTable('inactive')">Inactive</button>
                <button class="filter-btn inactive" onclick="filterTable('suspended')">Suspended</button>
            </div>

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
                        <tr>
                            <td><div class="doctor-name">Doc McStuffins</div></td>
                            <td>
                                <div class="contact-email">doc@doctorssg.com</div>
                                <div class="contact-location">Lagos, NG</div>
                            </td>
                            <td><div class="clinic-name">Martha Eye Center</div></td>
                            <td>249</td>
                            <td><span class="status-badge status-active">Active</span></td>
                            <td>2 hours ago</td>
                            <td>
                                <div class="action-links">
                                    <a href="#" class="action-link">Edit</a>
                                    <a href="#" class="action-link delete">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="doctor-name">Dr. Sarah Smith</div></td>
                            <td>
                                <div class="contact-email">sarah@healthcare.com</div>
                                <div class="contact-location">Abuja, NG</div>
                            </td>
                            <td><div class="clinic-name">Central Medical</div></td>
                            <td>156</td>
                            <td><span class="status-badge status-active">Active</span></td>
                            <td>1 hour ago</td>
                            <td>
                                <div class="action-links">
                                    <a href="#" class="action-link">Edit</a>
                                    <a href="#" class="action-link delete">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="doctor-name">Dr. James Wilson</div></td>
                            <td>
                                <div class="contact-email">james@healthcare.com</div>
                                <div class="contact-location">Lagos, NG</div>
                            </td>
                            <td><div class="clinic-name">City Hospital</div></td>
                            <td>203</td>
                            <td><span class="status-badge status-inactive">Inactive</span></td>
                            <td>5 days ago</td>
                            <td>
                                <div class="action-links">
                                    <a href="#" class="action-link">Edit</a>
                                    <a href="#" class="action-link delete">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="doctor-name">Dr. Amara Okafor</div></td>
                            <td>
                                <div class="contact-email">amara@healthcare.com</div>
                                <div class="contact-location">Lagos, NG</div>
                            </td>
                            <td><div class="clinic-name">West End Clinic</div></td>
                            <td>178</td>
                            <td><span class="status-badge status-active">Active</span></td>
                            <td>30 minutes ago</td>
                            <td>
                                <div class="action-links">
                                    <a href="#" class="action-link">Edit</a>
                                    <a href="#" class="action-link delete">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="doctor-name">Dr. Chioma Nwosu</div></td>
                            <td>
                                <div class="contact-email">chioma@healthcare.com</div>
                                <div class="contact-location">Enugu, NG</div>
                            </td>
                            <td><div class="clinic-name">Rainbow Hospital</div></td>
                            <td>92</td>
                            <td><span class="status-badge status-suspended">Suspended</span></td>
                            <td>2 weeks ago</td>
                            <td>
                                <div class="action-links">
                                    <a href="#" class="action-link">Edit</a>
                                    <a href="#" class="action-link delete">Delete</a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Admin Settings Section -->
        <div id="settings" class="content-section">
            <div class="settings-form">
                <div class="form-section">
                    <h3>Account Settings</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" placeholder="Enter full name">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" placeholder="Enter phone number">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Change Password</h3>
                    <div class="form-row">
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

                <div class="form-section">
                    <h3>Security Settings</h3>
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="twofa">
                        <div>
                            <label for="twofa" class="checkbox-label">Two-Factor Authentication</label>
                            <div class="checkbox-description">Add an extra layer of security</div>
                        </div>
                    </div>

                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="login-alerts">
                        <div>
                            <label for="login-alerts" class="checkbox-label">Login Alerts</label>
                            <div class="checkbox-description">Get notified of new login attempts</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>System Settings</h3>
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="system-twofa">
                        <div>
                            <label for="system-twofa" class="checkbox-label">Two-Factor Authentication</label>
                            <div class="checkbox-description">Add an extra layer of security</div>
                        </div>
                    </div>

                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="system-alerts">
                        <div>
                            <label for="system-alerts" class="checkbox-label">Login Alerts</label>
                            <div class="checkbox-description">Get notified of new login attempts</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Other Administrators</h3>
                    <div class="add-admin-section">
                        <button class="add-admin-btn">+ Add Admin</button>
                    </div>
                    <div class="admin-list">
                        <div class="admin-item">
                            <div>
                                <div class="doctor-name">John Doe</div>
                                <div class="admin-email">john.doe@example.com</div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span class="admin-status">Active</span>
                                <span class="admin-action">×</span>
                            </div>
                        </div>

                        <div class="admin-item">
                            <div>
                                <div class="doctor-name">Jane Smith</div>
                                <div class="admin-email">jane.smith@example.com</div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span class="admin-status">Active</span>
                                <span class="admin-action">×</span>
                            </div>
                        </div>

                        <div class="admin-item">
                            <div>
                                <div class="doctor-name">Mike Johnson</div>
                                <div class="admin-email">mike.johnson@example.com</div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span class="admin-status">Active</span>
                                <span class="admin-action">×</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="save-button">Save All Settings</button>
            </div>
        </div>
    </div>

    <script>
        function showSection(section) {
            // Hide all sections
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(s => s.classList.remove('active'));

            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(t => t.classList.remove('active'));

            // Show selected section
            document.getElementById(section).classList.add('active');

            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        function filterTable(status) {
            console.log('Filtering by:', status);
            // Filter logic will be implemented
        }
    </script>
</body>
</html>
