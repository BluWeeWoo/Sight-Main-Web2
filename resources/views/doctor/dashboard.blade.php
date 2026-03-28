<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-green: #527267;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
        }
        body { background-color: var(--bg-light); font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        
        .sidebar-container {
            background: white;
            border: none;
            border-radius: 1.5rem;
            height: calc(100vh - 120px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .patient-list { overflow-y: auto; flex-grow: 1; }
        .patient-list::-webkit-scrollbar { width: 6px; }
        .patient-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

        .patient-item {
            border: none !important;
            padding: 1rem 1.5rem;
            transition: all 0.2s;
            background: transparent;
            cursor: pointer;
        }
        .patient-item:hover { background-color: #f8fafc; }
        .patient-item.active {
            background-color: #f0f4f3 !important;
            position: relative;
        }
        .patient-item.active::after {
            content: "";
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 4px;
            background: var(--primary-green);
            border-radius: 0 4px 4px 0;
        }

        .patient-info { font-size: 0.85rem; }
        .patient-info strong { color: #1f2937; display: block; }
        .patient-info small { color: #6b7280; display: block; margin-top: 0.25rem; }
        .compliance-info { font-size: 0.7rem; color: #6b7280; margin-top: 0.5rem; display: flex; justify-content: space-between; }
        .compliance-bar { height: 4px; background: #e2e8f0; border-radius: 2px; margin-top: 0.35rem; overflow: hidden; }
        .compliance-bar-fill { height: 100%; background: var(--primary-green); width: 75%; }

        .card { border-radius: 1rem; border: none; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        .header-avatar { background-color: #f1f5f9; color: var(--primary-green); }
        
        .nav-tabs .nav-link {
            border: none;
            color: #64748b;
            font-weight: 500;
            padding: 0.75rem 1rem;
        }
        .nav-tabs .nav-link.active {
            color: var(--primary-green);
            border-bottom: 2px solid var(--primary-green);
            background: transparent;
        }

        .search-input {
            background-color: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
        }

        .health-grade-card { background-color: #f0faf8; border: none !important; }
        .health-badge { background: #dcfce7; color: #166534; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .metric-card { text-align: center; }
        .metric-value { font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0 0.25rem; }
        .metric-label { font-size: 0.75rem; color: #6b7280; }

        .info-card-header { font-weight: 600; margin-bottom: 1rem; font-size: 0.9rem; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.875rem; }
        .info-label { color: #6b7280; }
        .info-value { font-weight: 600; color: #1f2937; }

        .chart-container { position: relative; height: 300px; }
        
        .tab-content-container { max-height: calc(100vh - 120px); overflow-y: auto; }

        .activity-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.2s;
        }
        .activity-item:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-green);
        }
        .activity-icon {
            width: 48px;
            height: 48px;
            background-color: #dcfce7;
            border-radius: 0.5rem;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <header class="bg-white border-bottom sticky-top z-3">
        <div class="container-fluid px-4 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h4 fw-bold mb-0">Eye Health Dashboard</h1>
                    <p class="text-muted small mb-0">Patient monitoring system</p>
                </div>
                <div class="d-flex align-items-center gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle header-avatar d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                            {{ strtoupper(substr(auth()->user()->name ?? 'DR', 0, 2)) }}
                        </div>
                        <div class="d-none d-md-block">
                            <p class="small fw-semibold mb-0">{{ auth()->user()->name ?? 'Doctor' }}</p>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">{{ auth()->user()->specialty ?? 'Ophthalmologist' }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="container-fluid p-4">
        <div class="row g-4">
            <aside class="col-lg-3">
                <div class="sidebar-container">
                    <div class="p-4 border-bottom bg-white sticky-top">
                        <h2 class="h6 fw-bold mb-1">Patients</h2>
                        <p class="text-muted small mb-3">5 patients today</p>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control search-input border-start-0 ps-0" placeholder="Search patients...">
                        </div>
                    </div>
                    <div class="patient-list list-group list-group-flush">
                        <button class="list-group-item list-group-item-action patient-item active" onclick="selectPatient(this, 'Emma Rodriguez', 'Maria Rodriguez', 'ER')">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; background-color: var(--primary-green); flex-shrink: 0;">ER</div>
                                <div class="patient-info flex-grow-1">
                                    <strong>Emma Rodriguez</strong>
                                    <small>Maria Rodriguez</small>
                                    <div class="compliance-info">
                                        <span>20-20-20 Compliance</span>
                                        <span>75%</span>
                                    </div>
                                    <div class="compliance-bar">
                                        <div class="compliance-bar-fill"></div>
                                    </div>
                                </div>
                            </div>
                        </button>
                        <button class="list-group-item list-group-item-action patient-item" onclick="selectPatient(this, 'Juan Dela Cruz', 'Juan Sr.', 'JD')">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; background-color: var(--primary-green); flex-shrink: 0;">JD</div>
                                <div class="patient-info flex-grow-1">
                                    <strong>Juan Dela Cruz</strong>
                                    <small>Juan Sr.</small>
                                    <div class="compliance-info">
                                        <span>20-20-20 Compliance</span>
                                        <span>75%</span>
                                    </div>
                                    <div class="compliance-bar">
                                        <div class="compliance-bar-fill"></div>
                                    </div>
                                </div>
                            </div>
                        </button>
                        <button class="list-group-item list-group-item-action patient-item" onclick="selectPatient(this, 'Daniel Padilla', 'Daniel Sr.', 'DP')">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; background-color: var(--primary-green); flex-shrink: 0;">DP</div>
                                <div class="patient-info flex-grow-1">
                                    <strong>Daniel Padilla</strong>
                                    <small>Daniel Sr.</small>
                                    <div class="compliance-info">
                                        <span>20-20-20 Compliance</span>
                                        <span>75%</span>
                                    </div>
                                    <div class="compliance-bar">
                                        <div class="compliance-bar-fill"></div>
                                    </div>
                                </div>
                            </div>
                        </button>
                        <button class="list-group-item list-group-item-action patient-item" onclick="selectPatient(this, 'Kathryn Bernardo', 'Kathryn Sr.', 'KB')">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; background-color: var(--primary-green); flex-shrink: 0;">KB</div>
                                <div class="patient-info flex-grow-1">
                                    <strong>Kathryn Bernardo</strong>
                                    <small>Kathryn Sr.</small>
                                    <div class="compliance-info">
                                        <span>20-20-20 Compliance</span>
                                        <span>75%</span>
                                    </div>
                                    <div class="compliance-bar">
                                        <div class="compliance-bar-fill"></div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </aside>

            <div class="col-lg-9">
                <div class="tab-content-container">
                    <!-- Patient Header Card -->
                    <div class="card border-0 mb-4" style="background-color: #f1fcf9;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex gap-3">
                                    <div id="mainAvatar" class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold h2 mb-0" style="width: 64px; height: 64px; background-color: var(--primary-green);">ER</div>
                                    <div>
                                        <h3 class="h4 fw-bold mb-1" id="patientName">Emma Rodriguez</h3>
                                        <span class="badge bg-white text-muted border fw-normal text-dark">PT-2026-001</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="text-muted small mb-0">Guardian</p>
                                    <p class="fw-bold mb-0" id="guardianName">Maria Rodriguez</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs border-bottom-0 mb-4 gap-2" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview">Overview</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity">Activity Log</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="overview">
                            <!-- Health Grade Card -->
                            <div class="card health-grade-card mb-4">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-1">Overall Health Grade: Good</h6>
                                        <p class="text-muted small mb-0">Patient is maintaining excellent eye health habits</p>
                                    </div>
                                    <div class="text-center">
                                        <span class="health-badge">Good</span>
                                        <div class="h2 fw-bold mb-0 mt-2" style="color: var(--primary-green);">78%</div>
                                        <div class="small text-muted">Health Score</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Metrics Grid -->
                            <div class="row g-3 mb-4">
                                <div class="col-lg-3">
                                    <div class="card metric-card">
                                        <div class="card-body">
                                            <span class="health-badge">good</span>
                                            <div class="metric-value">78%</div>
                                            <div class="metric-label">Eye Health Score</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="card metric-card">
                                        <div class="card-body">
                                            <span class="health-badge">good</span>
                                            <div class="metric-value">2h 15m</div>
                                            <div class="metric-label">Avg. Daily Screen Time</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="card metric-card">
                                        <div class="card-body">
                                            <span class="health-badge">good</span>
                                            <div class="metric-value">12/min</div>
                                            <div class="metric-label">Avg. Blink Rate</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="card metric-card">
                                        <div class="card-body">
                                            <span class="health-badge">good</span>
                                            <div class="metric-value">52cm</div>
                                            <div class="metric-label">Avg. Viewing Distance</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Cards -->
                            <div class="row g-3 mb-4">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="info-card-header">20-20-20 Rule Compliance</div>
                                            <div class="info-row">
                                                <span class="info-label">Breaks taken:</span>
                                                <span class="info-value">18 / 24</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">Last 7 days</span>
                                                <span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="info-card-header">Strain Events (7 days)</div>
                                            <div class="info-row">
                                                <span class="info-label">Low blink rate events:</span>
                                                <span class="info-value">4</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">Distance violations</span>
                                                <span class="info-value">3</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Charts -->
                            <div class="card mb-4">
                                <div class="card-header bg-white border-0 py-3">
                                    <h6 class="fw-bold mb-0">7-Day Compliance Trends - Blink Rate & Viewing Distance</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="complianceChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header bg-white border-0 py-3">
                                    <h6 class="fw-bold mb-0">Daily Screen Time & Breaks Taken</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="screenTimeChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header bg-white border-0 py-3">
                                    <h6 class="fw-bold mb-0">Eye Health Score Trend</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="healthScoreChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-light w-100 text-start p-3 border-3" style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Send Recommendations to Patient</span>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                            </button>
                        </div>

                        <!-- Activity Tab -->
                        <div class="tab-pane fade" id="activity">
                            <h5 class="fw-bold mb-4">Recent Activity</h5>
                            
                            <div class="activity-item mb-3">
                                <div class="d-flex gap-3">
                                    <div class="activity-icon"></div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 fw-bold" style="color: #1f2937;">Completed 20-20-20 break</p>
                                        <small class="text-muted">Looked away for 20 seconds</small>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item mb-3">
                                <div class="d-flex gap-3">
                                    <div class="activity-icon"></div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 fw-bold" style="color: #1f2937;">Low blink rate detected</p>
                                        <small class="text-muted">Blink rate dropped to 5/min during gaming session</small>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item mb-3">
                                <div class="d-flex gap-3">
                                    <div class="activity-icon"></div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 fw-bold" style="color: #1f2937;">Viewing distance improved</p>
                                        <small class="text-muted">Average distance increased from 32cm to 48cm 5 hours ago</small>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item mb-4">
                                <div class="d-flex gap-3">
                                    <div class="activity-icon"></div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 fw-bold" style="color: #1f2937;">Completed eye exercise</p>
                                        <small class="text-muted">3 sets of eye rolling exercises</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden Activity Items -->
                            <div id="moreActivityItems" style="display: none;">
                                <div class="activity-item mb-3">
                                    <div class="d-flex gap-3">
                                        <div class="activity-icon"></div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 fw-bold" style="color: #1f2937;">Screen time limit reached</p>
                                            <small class="text-muted">Daily screen time exceeded 6 hours</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="activity-item mb-3">
                                    <div class="d-flex gap-3">
                                        <div class="activity-icon"></div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 fw-bold" style="color: #1f2937;">Eye strain warning</p>
                                            <small class="text-muted">Continuous screen time without breaks detected</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="activity-item mb-3">
                                    <div class="d-flex gap-3">
                                        <div class="activity-icon"></div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 fw-bold" style="color: #1f2937;">Posture corrected</p>
                                            <small class="text-muted">Viewing distance normalized after adjustment</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="activity-item mb-4">
                                    <div class="d-flex gap-3">
                                        <div class="activity-icon"></div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 fw-bold" style="color: #1f2937;">Health report generated</p>
                                            <small class="text-muted">Weekly compliance report ready for review</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn w-100 mb-4" id="loadMoreBtn" onclick="toggleMoreActivity()" style="color: var(--primary-green); background-color: #f1fcf9; border: 1px solid #a8e6e0; font-weight: 600;">
                                Load More Activity
                            </button>

                            <button class="btn btn-light w-100 text-start p-3 border-3" style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Send Recommendations to Patient</span>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectPatient(element, name, guardian, initials) {
            document.querySelectorAll('.patient-item').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('patientName').innerText = name;
            document.getElementById('guardianName').innerText = guardian;
            document.getElementById('mainAvatar').innerText = initials;
        }

        function toggleMoreActivity() {
            const moreItems = document.getElementById('moreActivityItems');
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            
            if (moreItems.style.display === 'none') {
                moreItems.style.display = 'block';
                loadMoreBtn.textContent = 'Show Less Activity';
            } else {
                moreItems.style.display = 'none';
                loadMoreBtn.textContent = 'Load More Activity';
            }
        }

        // Initialize Charts
        new Chart(document.getElementById('complianceChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Blink Rate',
                        data: [35, 38, 32, 40, 38, 30, 35],
                        borderColor: '#527267',
                        backgroundColor: 'rgba(82, 114, 103, 0.05)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#527267'
                    },
                    {
                        label: 'Distance',
                        data: [45, 48, 50, 47, 49, 55, 52],
                        borderColor: '#a8e6e0',
                        backgroundColor: 'rgba(168, 230, 224, 0.05)',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#a8e6e0',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { position: 'left', max: 60 },
                    y1: { position: 'right', max: 60, grid: { drawOnChartArea: false } }
                }
            }
        });

        new Chart(document.getElementById('screenTimeChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Breaks Taken',
                        data: [18, 20, 16, 22, 18, 8, 12],
                        backgroundColor: '#527267',
                        borderRadius: 4
                    },
                    {
                        label: 'Screen Time (min)',
                        data: [120, 140, 130, 150, 120, 60, 90],
                        backgroundColor: '#a8e6e0',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        new Chart(document.getElementById('healthScoreChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Health Score (%)',
                    data: [75, 72, 80, 78, 76, 72, 78],
                    borderColor: '#527267',
                    backgroundColor: 'rgba(82, 114, 103, 0.05)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#527267',
                    pointBorderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { min: 0, max: 100, ticks: { stepSize: 25 } } }
            }
        });

        document.getElementById('searchInput').addEventListener('input', function(e) {
            const val = e.target.value.toLowerCase();
            document.querySelectorAll('.patient-item').forEach(item => {
                const name = item.innerText.toLowerCase();
                item.style.display = name.includes(val) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
