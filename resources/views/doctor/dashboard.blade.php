<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Doctor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #527267;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
        }
        body { 
            background: 
                radial-gradient(circle at 90% 50%, rgba(42, 131, 68, 0.2) 0%, transparent 35%),
                radial-gradient(circle at 50% 50%, #E4FFD8 0%, transparent 60%),
                radial-gradient(circle at 15% 20%, rgba(251, 207, 232, 0.6) 0%, transparent 20%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        .bg-lumi-text {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 30vw;
            font-weight: 900;
            font-family: 'Fredoka', sans-serif;
            color: #E4FFD8;
            z-index: -1;
            letter-spacing: 25px;
            user-select: none;
            transition: all 0.6s ease;
            -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.2);
            text-shadow: 
                5px 15px 30px rgba(0, 0, 0, 0.05),
                -1px -1px 0 rgba(255, 255, 255, 0.4);
            pointer-events: none;
        }
        
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
    <div class="bg-lumi-text">LUMI</div>
    <header class="bg-white border-bottom sticky-top z-3">
        <div class="container-fluid px-4 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h4 fw-bold mb-0">Eye Health Dashboard</h1>
                    <p class="text-muted small mb-0">Patient monitoring system</p>
                </div>
                <div class="d-flex align-items-center gap-4">
                    <button class="btn btn-light position-relative rounded-circle p-2" data-bs-toggle="modal" data-bs-target="#requestsModal" style="border: 1px solid var(--border-color);">
                        <i class="bi bi-bell fs-5 text-secondary"></i>
                        @if(isset($pendingRequests) && count($pendingRequests) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ count($pendingRequests) }}
                            </span>
                        @endif
                    </button>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle header-avatar d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                            {{ auth()->user()->initials ?? 'DR' }}
                        </div>
                        <div class="d-none d-md-block">
                            <p class="small fw-semibold mb-0">{{ auth()->user()->display_name ?? 'Doctor' }}</p>
                            <p class="text-muted mb-0" style="font-size: 0.7rem;">{{ $doctorProfile->specialty ?? 'Ophthalmologist' }}</p>
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
                        <p class="text-muted small mb-3">{{ count($patients) }} patient{{ count($patients) !== 1 ? 's' : '' }}</p>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control search-input border-start-0 ps-0" placeholder="Search patients...">
                        </div>
                    </div>
                    <div class="patient-list list-group list-group-flush">
                        @if(count($patients) > 0)
                            @foreach($patients as $patient)
                                @php
                                    $patientCompliance = $patient['compliance_percent'];
                                    $complianceWidth = $patientCompliance !== null ? min(max($patientCompliance, 0), 100) : 0;
                                @endphp
                                <a href="{{ route('doctor.dashboard', ['patient' => $patient['id']]) }}" class="list-group-item list-group-item-action patient-item text-decoration-none {{ ($selectedPatient['id'] ?? null) === $patient['id'] ? 'active' : '' }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; background-color: var(--primary-green); flex-shrink: 0;">{{ $patient['initials'] }}</div>
                                        <div class="patient-info flex-grow-1">
                                            <strong>{{ $patient['name'] }}</strong>
                                            <small>{{ $patient['guardian'] }}</small>
                                            <div class="compliance-info">
                                                <span>20-20-20 Compliance</span>
                                                <span>{{ $patientCompliance !== null ? $patientCompliance . '%' : '--' }}</span>
                                            </div>
                                            <div class="compliance-bar">
                                                <div class="compliance-bar-fill" style="width: <?php echo e($complianceWidth); ?>%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        @else
                            <div class="p-4 text-center text-muted" style="flex-grow: 1; display: flex; align-items: center; justify-content: center;">
                                <div>
                                    <p class="mb-0">No patients assigned yet</p>
                                </div>
                            </div>
                        @endif
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
                                    <div id="mainAvatar" class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold h2 mb-0" style="width: 64px; height: 64px; background-color: var(--primary-green);">{{ $selectedPatient['initials'] ?? 'PT' }}</div>
                                    <div>
                                        <h3 class="h4 fw-bold mb-1" id="patientName">{{ $selectedPatient['name'] ?? 'Select a Patient' }}</h3>
                                        @if(!empty($selectedPatient['patient_code']))
                                            <span class="badge bg-white text-muted border fw-normal text-dark">{{ $selectedPatient['patient_code'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="text-muted small mb-0">Guardian</p>
                                    <p class="fw-bold mb-0" id="guardianName">{{ $selectedPatient['guardian'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('doctor.components.pending-requests-panel')

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
                                        <h6 class="fw-bold mb-1">Overall Health Grade: {{ $dashboardData['health_grade'] ?? 'N/A' }}</h6>
                                        <p class="text-muted small mb-0">Live summary for the selected patient based on the last 7 days of records.</p>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <!-- Health Score -->
                                        <div class="text-center pe-4 border-end">
                                            <span class="health-badge">{{ $dashboardData['health_grade'] ?? 'N/A' }}</span>
                                            <div class="h2 fw-bold mb-0 mt-2" style="color: var(--primary-green);">{{ $dashboardData['health_score_display'] ?? '--' }}</div>
                                            <div class="small text-muted">Health Score</div>
                                        </div>
                                        <!-- Coins Balance -->
                                        <div class="text-center ps-4">
                                            <span class="health-badge" style="background-color: #fef08a; color: #b45309;"><i class="bi bi-coin"></i> Economy</span>
                                            <div class="h2 fw-bold mb-0 mt-2" style="color: #d97706;">{{ number_format($dashboardData['latest_coins'] ?? 0) }}</div>
                                            <div class="small text-muted">Total Coins</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Metrics Grid -->
                            <div class="row g-3 mb-4">
                                <div class="col-lg-3">
                                    <div class="card metric-card">
                                        <div class="card-body">
                                            <span class="health-badge">{{ $dashboardData['health_grade'] ?? 'N/A' }}</span>
                                            <div class="metric-value">{{ $dashboardData['health_score_display'] ?? '--' }}</div>
                                            <div class="metric-label">Eye Health Score</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="card metric-card">
                                        <div class="card-body">
                                            <span class="health-badge">{{ $dashboardData['health_grade'] ?? 'N/A' }}</span>
                                            <div class="metric-value">{{ $dashboardData['screen_time_display'] ?? '0m' }}</div>
                                            <div class="metric-label">Avg. Daily Screen Time</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="card metric-card">
                                        <div class="card-body">
                                            <span class="health-badge">{{ $dashboardData['health_grade'] ?? 'N/A' }}</span>
                                            <div class="metric-value">{{ $dashboardData['blink_rate_display'] ?? '--/min' }}</div>
                                            <div class="metric-label">Avg. Blink Rate</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="card metric-card">
                                        <div class="card-body">
                                            <span class="health-badge">{{ $dashboardData['health_grade'] ?? 'N/A' }}</span>
                                            <div class="metric-value">{{ $dashboardData['distance_display'] ?? '--cm' }}</div>
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
                                            <div class="info-card-header">Screen-Time Target Compliance</div>
                                            <div class="info-row">
                                                <span class="info-label">Days within target:</span>
                                                <span class="info-value">{{ $dashboardData['target_days_display'] ?? '0/7' }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">Last 7 days</span>
                                                <span class="info-value">Live data</span>
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
                                                <span class="info-value">{{ $dashboardData['low_blink_events'] ?? 0 }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">Distance violations</span>
                                                <span class="info-value">{{ $dashboardData['distance_violations'] ?? 0 }}</span>
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
                                    <h6 class="fw-bold mb-0">Daily Screen Time & Strain Events</h6>
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

                            <button
                                class="btn btn-light w-100 text-start p-3 border-3"
                                style="border-style: dashed !important; border-color: #e2e8f0 !important;"
                                data-bs-toggle="modal"
                                data-bs-target="#recommendationModal"
                            >
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Send Recommendations to Patient</span>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                            </button>
                        </div>

                        <!-- Activity Tab -->
                        <div class="tab-pane fade" id="activity">
                            <h5 class="fw-bold mb-4">Recent Activity</h5>
                            @if(!empty($dashboardData['activity_items']))
                                @foreach($dashboardData['activity_page_items'] as $activity)
                                    <div class="activity-item mb-3">
                                        <div class="d-flex gap-3">
                                            <div class="activity-icon d-flex align-items-center justify-content-center text-success">
                                                <i class="bi bi-{{ $activity['icon'] }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-1 fw-bold" style="color: #1f2937;">{{ $activity['title'] }}</p>
                                                <small class="text-muted">{{ $activity['detail'] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if(($dashboardData['activity_total_pages'] ?? 1) > 1)
                                    <nav aria-label="Activity pagination" class="mt-4 mb-4">
                                        <ul class="pagination justify-content-center mb-0">
                                            <li class="page-item {{ ($dashboardData['activity_page'] ?? 1) <= 1 ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ route('doctor.dashboard', ['patient' => $selectedPatient['id'] ?? null, 'activity_page' => max(($dashboardData['activity_page'] ?? 1) - 1, 1), 'tab' => 'activity']) }}">Previous</a>
                                            </li>
                                            @for($page = 1; $page <= ($dashboardData['activity_total_pages'] ?? 1); $page++)
                                                <li class="page-item {{ ($dashboardData['activity_page'] ?? 1) === $page ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ route('doctor.dashboard', ['patient' => $selectedPatient['id'] ?? null, 'activity_page' => $page, 'tab' => 'activity']) }}">{{ $page }}</a>
                                                </li>
                                            @endfor
                                            <li class="page-item {{ ($dashboardData['activity_page'] ?? 1) >= ($dashboardData['activity_total_pages'] ?? 1) ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ route('doctor.dashboard', ['patient' => $selectedPatient['id'] ?? null, 'activity_page' => min(($dashboardData['activity_page'] ?? 1) + 1, ($dashboardData['activity_total_pages'] ?? 1)), 'tab' => 'activity']) }}">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                @endif
                            @else
                                <div class="activity-item mb-4">
                                    <div class="d-flex gap-3">
                                        <div class="activity-icon d-flex align-items-center justify-content-center text-success">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 fw-bold" style="color: #1f2937;">No recent activity</p>
                                            <small class="text-muted">This patient has not generated any metric events yet.</small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <button
                                class="btn btn-light w-100 text-start p-3 border-3"
                                style="border-style: dashed !important; border-color: #e2e8f0 !important;"
                                data-bs-toggle="modal"
                                data-bs-target="#recommendationModal"
                            >
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

    <!-- Pending Requests Modal -->
    <div class="modal fade" id="requestsModal" tabindex="-1" aria-labelledby="requestsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="requestsModalLabel">Pending Requests</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(isset($pendingRequests) && count($pendingRequests) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($pendingRequests as $req)
                                <div class="list-group-item px-0 py-3 border-bottom" id="request-{{ $req->link_id }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 48px; height: 48px;">
                                                {{ substr($req->child_first_name, 0, 1) }}{{ substr($req->child_last_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $req->child_first_name }} {{ $req->child_last_name }}</h6>
                                                <small class="text-muted">Guardian: {{ $req->guardian_first_name }} {{ $req->guardian_last_name }}</small>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="respondToRequest({{ $req->link_id }}, 'decline')">Decline</button>
                                            <button class="btn btn-sm btn-success rounded-pill px-3" onclick="respondToRequest({{ $req->link_id }}, 'accept')">Accept</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 mb-2 d-block opacity-50"></i>
                            <p class="mb-0">No pending connection requests.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="recommendationModal" tabindex="-1" aria-labelledby="recommendationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recommendationModalLabel">Send Recommendation to Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="recommendationForm">
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Patient: <strong>{{ $selectedPatient['name'] ?? 'N/A' }}</strong>
                        </p>
                        <div class="mb-0">
                            <label for="recommendationText" class="form-label">Recommendation</label>
                            <textarea class="form-control" id="recommendationText" rows="5" maxlength="2000" placeholder="Type your recommendation here..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="submitRecommendationBtn">Send Recommendation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const selectedPatientId = <?php echo (int) ($selectedPatient['id'] ?? 0); ?>;
        const recommendationForm = document.getElementById('recommendationForm');
        const recommendationText = document.getElementById('recommendationText');
        const submitRecommendationBtn = document.getElementById('submitRecommendationBtn');
        const recommendationModalEl = document.getElementById('recommendationModal');

        // Handle Accept/Decline requests
        async function respondToRequest(linkId, action) {
            try {
                // Determine the correct status value based on your backend logic
                // Usually, 1 = Active/Accepted, whereas declining might just delete the row.
                const statusValue = action === 'accept' ? 1 : -1; 

                const response = await fetch(`/doctor/requests/${linkId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: statusValue, action: action })
                });

                const data = await response.json();

                if (response.ok) {
                    showDashboardAlert('success', `Request ${action}ed successfully.`);
                    
                    // Remove the item from the modal visually
                    const reqElement = document.getElementById(`request-${linkId}`);
                    if (reqElement) reqElement.remove();
                    
                    // Reload the page after a short delay so the new patient appears in the sidebar!
                    if (action === 'accept') {
                        setTimeout(() => window.location.reload(), 1500);
                    }
                } else {
                    showDashboardAlert('error', data.message || `Failed to ${action} request.`);
                }
            } catch (error) {
                showDashboardAlert('error', `An error occurred: ${error.message}`);
            }
        }

        function showDashboardAlert(type, message) {
            const cls = type === 'success' ? 'alert-success' : 'alert-danger';
            const alert = document.createElement('div');
            alert.className = `alert ${cls} alert-dismissible fade show position-fixed`;
            alert.style.top = '80px';
            alert.style.right = '20px';
            alert.style.zIndex = '1060';
            alert.style.maxWidth = '420px';
            alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }

        if (recommendationForm) {
            recommendationForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!selectedPatientId) {
                    showDashboardAlert('error', 'No patient selected.');
                    return;
                }

                const plan = recommendationText.value.trim();
                if (!plan) {
                    showDashboardAlert('error', 'Recommendation text is required.');
                    return;
                }

                submitRecommendationBtn.disabled = true;
                submitRecommendationBtn.textContent = 'Sending...';

                try {
                    const response = await fetch(`/doctor/patient/${selectedPatientId}/health-plan`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ plan })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Failed to send recommendation.');
                    }

                    showDashboardAlert('success', 'Recommendation sent successfully.');
                    recommendationText.value = '';
                    const modalInstance = bootstrap.Modal.getInstance(recommendationModalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } catch (error) {
                    showDashboardAlert('error', error.message || 'Failed to send recommendation.');
                } finally {
                    submitRecommendationBtn.disabled = false;
                    submitRecommendationBtn.textContent = 'Send Recommendation';
                }
            });
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'activity') {
            const activityTabTrigger = document.querySelector('[data-bs-target="#activity"]');
            if (activityTabTrigger) {
                bootstrap.Tab.getOrCreateInstance(activityTabTrigger).show();
            }
        }

        // Initialize Charts
        const chartLabels = <?php echo json_encode($dashboardData['labels'] ?? []); ?>;
        const blinkRates = <?php echo json_encode($dashboardData['blink_rates'] ?? []); ?>;
        const distances = <?php echo json_encode($dashboardData['distances'] ?? []); ?>;
        const screenTimes = <?php echo json_encode($dashboardData['screen_times'] ?? []); ?>;
        const strainEvents = <?php echo json_encode($dashboardData['strain_events'] ?? []); ?>;
        const healthScores = <?php echo json_encode($dashboardData['health_scores'] ?? []); ?>;

        if (chartLabels.length) {
            new Chart(document.getElementById('complianceChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [
                        {
                            label: 'Blink Rate',
                            data: blinkRates,
                            borderColor: '#527267',
                            backgroundColor: 'rgba(82, 114, 103, 0.05)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#527267'
                        },
                        {
                            label: 'Distance',
                            data: distances,
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
                    labels: chartLabels,
                    datasets: [
                        {
                            label: 'Screen Time (min)',
                            data: screenTimes,
                            backgroundColor: '#527267',
                            borderRadius: 4
                        },
                        {
                            label: 'Strain Events',
                            data: strainEvents,
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
                    labels: chartLabels,
                    datasets: [{
                        label: 'Health Score (%)',
                        data: healthScores,
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
        }

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
