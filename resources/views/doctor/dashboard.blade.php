<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eye Health Dashboard - Lumi</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .header-left h1 {
            font-size: 20px;
            font-weight: 600;
        }

        .header-left p {
            font-size: 13px;
            color: #999;
            margin-top: 2px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-info p {
            font-weight: 600;
            font-size: 14px;
        }

        .user-info span {
            font-size: 12px;
            color: #999;
        }

        .logout-btn {
            background: #1abc9c;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .logout-btn:hover {
            background: #16a085;
        }

        .container {
            display: flex;
            gap: 20px;
            padding: 20px 30px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .sidebar {
            width: 240px;
        }

        .main {
            flex: 1;
            overflow-y: auto;
            max-height: calc(100vh - 90px);
        }

        .patients-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .patients-box h3 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .patients-count {
            font-size: 24px;
            font-weight: bold;
            color: #1abc9c;
            margin-bottom: 15px;
        }

        .search-box {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .patient-item {
            padding: 12px;
            border: 2px solid transparent;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .patient-item:hover {
            background: #f5f5f5;
        }

        .patient-item.active {
            border-color: #1abc9c;
            background: #f0faf8;
        }

        .patient-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            font-weight: bold;
            color: #666;
        }

        .patient-name {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .patient-id {
            font-size: 11px;
            color: #999;
            margin-bottom: 5px;
        }

        .patient-compliance {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }

        .compliance-bar {
            width: 100%;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
        }

        .compliance-fill {
            height: 100%;
            background: #1abc9c;
            width: 75%;
        }

        .patient-header {
            background: #e8f7f4;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .patient-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .patient-avatar-large {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #d0e8e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            color: #1abc9c;
        }

        .patient-details h2 {
            font-size: 18px;
            margin-bottom: 3px;
        }

        .patient-details p {
            font-size: 12px;
            color: #999;
            margin-bottom: 2px;
        }

        .guardian-info {
            text-align: right;
            font-size: 12px;
        }

        .guardian-info p {
            margin: 2px 0;
        }

        .guardian-name {
            font-weight: 600;
            color: #333;
        }

        .tabs {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .tab {
            padding: 15px 0;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: #999;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab.active {
            color: #333;
            border-bottom-color: #1abc9c;
        }

        .tab:hover {
            color: #333;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .health-grade {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .health-grade-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .grade-label {
            font-size: 13px;
            color: #666;
        }

        .grade-value {
            font-size: 32px;
            font-weight: bold;
            color: #1abc9c;
        }

        .grade-badge {
            background: #1abc9c;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .grade-text {
            color: #1abc9c;
            font-weight: 600;
            margin-top: 10px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .metric-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }

        .metric-label {
            font-size: 12px;
            color: #999;
            margin-bottom: 10px;
        }

        .metric-value {
            font-size: 28px;
            font-weight: bold;
            color: #1abc9c;
        }

        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .chart-title {
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
        }

        .action-button {
            width: 100%;
            padding: 15px;
            background: #1abc9c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }

        .action-button:hover {
            background: #16a085;
        }

        @media (max-width: 1024px) {
            .metrics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>Eye Health Dashboard</h1>
            <p>Patient monitoring system</p>
        </div>
        <div class="header-right">
            <div class="user-info">
                <p>{{ auth()->user()->name ?? 'Dr. Martinez' }}</p>
                <span>Ophthalmologist</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="logout-btn">Sign Out</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <div class="patients-box">
                <h3>Patients</h3>
                <div class="patients-count">5</div>
                <input type="text" class="search-box" placeholder="Search patients...">

                <div class="patient-item active">
                    <div class="patient-avatar">ER</div>
                    <div class="patient-name">Emma Rodriguez</div>
                    <div class="patient-id">ID: PT-20206-001</div>
                    <div class="patient-compliance">20-20-20 Compliance</div>
                    <div class="compliance-bar">
                        <div class="compliance-fill"></div>
                    </div>
                    <div style="font-size: 11px; color: #999; margin-top: 3px;">75%</div>
                </div>

                <div class="patient-item">
                    <div class="patient-avatar">JD</div>
                    <div class="patient-name">Juan Dela Cruz</div>
                    <div class="patient-id">ID: PT-20206-002</div>
                    <div class="patient-compliance">20-20-20 Compliance</div>
                    <div class="compliance-bar">
                        <div class="compliance-fill"></div>
                    </div>
                    <div style="font-size: 11px; color: #999; margin-top: 3px;">75%</div>
                </div>

                <div class="patient-item">
                    <div class="patient-avatar">DP</div>
                    <div class="patient-name">Daniel Padilla</div>
                    <div class="patient-id">ID: PT-20206-003</div>
                    <div class="patient-compliance">20-20-20 Compliance</div>
                    <div class="compliance-bar">
                        <div class="compliance-fill"></div>
                    </div>
                    <div style="font-size: 11px; color: #999; margin-top: 3px;">75%</div>
                </div>

                <div class="patient-item">
                    <div class="patient-avatar">KB</div>
                    <div class="patient-name">Kathryn Bernardo</div>
                    <div class="patient-id">ID: PT-20206-004</div>
                    <div class="patient-compliance">20-20-20 Compliance</div>
                    <div class="compliance-bar">
                        <div class="compliance-fill"></div>
                    </div>
                    <div style="font-size: 11px; color: #999; margin-top: 3px;">75%</div>
                </div>
            </div>
        </div>

        <div class="main">
            <div class="patient-header">
                <div class="patient-info">
                    <div class="patient-avatar-large">ER</div>
                    <div class="patient-details">
                        <h2>Emma Rodriguez</h2>
                        <p>D: PT-20206-001</p>
                    </div>
                </div>
                <div class="guardian-info">
                    <p><strong>Guardian</strong></p>
                    <p class="guardian-name">Maria Rodriguez</p>
                    <p>maria@email.com</p>
                </div>
            </div>

            <div class="tabs">
                <div class="tab active">Overview</div>
                <div class="tab">Activity Log</div>
            </div>

            <div class="overview-grid">
                <div class="health-grade">
                    <div class="health-grade-header">
                        <div>
                            <div class="grade-label">Overall Health Grade</div>
                            <div class="grade-text">Patient is maintaining excellent eye health habits</div>
                        </div>
                        <div style="text-align: right;">
                            <div class="grade-badge">Excellent</div>
                            <div class="grade-value">78%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-label">Duration</div>
                    <div class="metric-value">2h 15m</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Breaks</div>
                    <div class="metric-value">12/min</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Distance</div>
                    <div class="metric-value">52cm</div>
                </div>
            </div>

            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-label">Strain Events (7 days)</div>
                    <div class="metric-value">3</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Blink Rate & Viewing Distance</div>
                    <div class="metric-value" style="color: #1abc9c;">Good</div>
                </div>
                <div></div>
            </div>

            <div class="chart-container">
                <div class="chart-title">7-Day Compliance Trends - Blink Rate & Viewing Distance</div>
                <div class="chart-wrapper">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-title">Daily Screen Time & Breaks Taken</div>
                <div class="chart-wrapper">
                    <canvas id="screenTimeChart"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-title">Eye Health Score Trend</div>
                <div class="chart-wrapper">
                    <canvas id="healthScoreChart"></canvas>
                </div>
            </div>

            <button class="action-button">Send Personalized Health Plan</button>
        </div>
    </div>

    <script>
        // 7-Day Compliance Trends
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Compliance',
                    data: [95, 92, 95, 92, 90, 78, 82],
                    borderColor: '#1abc9c',
                    backgroundColor: 'rgba(26, 188, 156, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 6,
                    pointBackgroundColor: '#1abc9c',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { max: 100, min: 0 }
                }
            }
        });

        // Daily Screen Time & Breaks
        const screenTimeCtx = document.getElementById('screenTimeChart').getContext('2d');
        new Chart(screenTimeCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'work',
                        data: [6, 7, 8, 6, 7, 2, 1],
                        backgroundColor: '#1abc9c'
                    },
                    {
                        label: 'leisure',
                        data: [3, 2, 2, 3, 2, 5, 4],
                        backgroundColor: '#7dd3c0'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Eye Health Score Trend
        const healthScoreCtx = document.getElementById('healthScoreChart').getContext('2d');
        new Chart(healthScoreCtx, {
            type: 'line',
            data: {
                labels: ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7'],
                datasets: [{
                    label: 'Health Score',
                    data: [75, 76, 76, 76, 77, 77, 78],
                    borderColor: '#1abc9c',
                    backgroundColor: 'rgba(26, 188, 156, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#1abc9c'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { max: 100, min: 0 }
                }
            }
        });
    </script>
</body>
</html>
