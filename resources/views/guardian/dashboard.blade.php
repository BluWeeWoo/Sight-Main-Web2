<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian Dashboard - SIGHT</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        [x-cloak] { display: none; }
        .tab-indicator {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-50" x-data="guardianDashboard()" x-cloak>
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Guardian Dashboard</h1>
                        <p class="text-gray-600 mt-1">Monitor your child's eye health and manage screen time rules</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Child: <span class="font-semibold text-gray-900" x-text="childName"></span></p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Container -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Tab Navigation -->
            <div class="bg-white rounded-lg shadow-md p-1 flex gap-2 mb-8">
                <button 
                    @click="activeTab = 'overview'" 
                    :class="activeTab === 'overview' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:text-gray-900'"
                    class="px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                    Overview
                </button>
                <button 
                    @click="activeTab = 'analytics'" 
                    :class="activeTab === 'analytics' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:text-gray-900'"
                    class="px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                    Analytics
                </button>
                <button 
                    @click="activeTab = 'controls'" 
                    :class="activeTab === 'controls' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:text-gray-900'"
                    class="px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                    Controls
                </button>
            </div>

            <!-- Overview Tab -->
            <div x-show="activeTab === 'overview'" class="space-y-6">
                
                <!-- Eye Health Score Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Eye Health Score</h3>
                        <div class="flex justify-center items-center" style="height: 200px;">
                            <canvas id="eyeHealthChart"></canvas>
                        </div>
                        <div class="mt-4">
                            <p class="text-3xl font-bold text-indigo-600 text-center" x-text="eyeHealthScore"></p>
                            <p class="text-sm text-gray-500 text-center mt-2">out of 100</p>
                        </div>
                    </div>

                    <!-- Stat Cards -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Screen Time -->
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-blue-100">Today's Screen Time</p>
                                        <p class="text-3xl font-bold mt-2" x-text="todayScreenTime + ' min'"></p>
                                    </div>
                                    <svg class="w-12 h-12 opacity-30" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4V5h12v10z"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Blink Rate -->
                            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-green-100">Avg Blink Rate</p>
                                        <p class="text-3xl font-bold mt-2" x-text="avgBlinkRate + ' bpm'"></p>
                                    </div>
                                    <svg class="w-12 h-12 opacity-30" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Eye Distance -->
                            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-purple-100">Avg Eye Distance</p>
                                        <p class="text-3xl font-bold mt-2" x-text="avgEyeDistance + ' cm'"></p>
                                    </div>
                                    <svg class="w-12 h-12 opacity-30" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Strain Events -->
                            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-md p-6 text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-red-100">Strain Events Today</p>
                                        <p class="text-3xl font-bold mt-2" x-text="strainEventsToday"></p>
                                    </div>
                                    <svg class="w-12 h-12 opacity-30" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 20-20-20 Break Compliance -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">20-20-20 Break Compliance</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="mb-4">
                                <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-100 rounded-full">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-indigo-600" x-text="breakCompliancePercent + '%'"></p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-gray-600">Compliance Rate</p>
                            <p class="text-3xl font-bold text-indigo-600 mt-2" x-text="breaksCompleted + ' of ' + breaksScheduled"></p>
                            <p class="text-sm text-gray-500 mt-1">breaks completed today</p>
                        </div>
                        <div class="md:col-span-2 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-3 font-medium">Recommended: Every 20 minutes, look 20 feet away for 20 seconds</p>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Last break taken:</span>
                                    <span class="font-semibold text-gray-900" x-text="lastBreakTime"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Next suggested break:</span>
                                    <span class="font-semibold text-gray-900" x-text="nextBreakTime"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div x-show="activeTab === 'analytics'" class="space-y-6">
                
                <!-- Weekly Trends -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Weekly Eye Health Trends</h3>
                    <div style="height: 300px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Metrics Comparison -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Screen Time Trend</h4>
                        <p class="text-3xl font-bold text-blue-600 mb-2" x-text="avgWeeklyScreenTime + ' min'"></p>
                        <p class="text-sm text-gray-500">7-day average</p>
                        <div class="mt-3">
                            <div class="flex items-center gap-2">
                                <span :class="screenTimeTrend > 0 ? 'text-red-500' : 'text-green-500'" class="text-sm font-semibold" x-text="(screenTimeTrend > 0 ? '+' : '') + screenTimeTrend + '%'"></span>
                                <span class="text-xs text-gray-500">vs previous week</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Blink Rate Trend</h4>
                        <p class="text-3xl font-bold text-green-600 mb-2" x-text="avgWeeklyBlinkRate + ' bpm'"></p>
                        <p class="text-sm text-gray-500">7-day average</p>
                        <div class="mt-3">
                            <div class="flex items-center gap-2">
                                <span :class="blinkRateTrend > 0 ? 'text-green-500' : 'text-red-500'" class="text-sm font-semibold" x-text="(blinkRateTrend > 0 ? '+' : '') + blinkRateTrend + '%'"></span>
                                <span class="text-xs text-gray-500">vs previous week</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Eye Distance Trend</h4>
                        <p class="text-3xl font-bold text-purple-600 mb-2" x-text="avgWeeklyEyeDistance + ' cm'"></p>
                        <p class="text-sm text-gray-500">7-day average</p>
                        <div class="mt-3">
                            <div class="flex items-center gap-2">
                                <span :class="eyeDistanceTrend > 0 ? 'text-green-500' : 'text-red-500'" class="text-sm font-semibold" x-text="(eyeDistanceTrend > 0 ? '+' : '') + eyeDistanceTrend + '%'"></span>
                                <span class="text-xs text-gray-500">vs previous week</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls Tab -->
            <div x-show="activeTab === 'controls'" class="space-y-6">
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Manage Screen Time Rules</h3>
                    
                    <form @submit.prevent="updateLimits()" class="space-y-6">
                        
                        <!-- Daily Screen Time Limit -->
                        <div class="border-b pb-6">
                            <label class="block text-sm font-medium text-gray-900 mb-2">
                                Daily Screen Time Limit: <span class="text-indigo-600 font-bold" x-text="limits.daily_limit_minutes + ' minutes'"></span>
                            </label>
                            <input 
                                type="range" 
                                min="1" 
                                max="1440" 
                                x-model.number="limits.daily_limit_minutes"
                                class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                <span>1 min</span>
                                <span>24 hours</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-3">
                                This is the maximum amount of time your child can use the device per day. The app will show warnings as they approach the limit.
                            </p>
                        </div>

                        <!-- Mode Selection -->
                        <div class="border-b pb-6">
                            <label class="block text-sm font-medium text-gray-900 mb-3">Enforcement Mode</label>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer" :class="limits.mode === 'Relaxed' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                    <input 
                                        type="radio" 
                                        value="Relaxed" 
                                        x-model="limits.mode"
                                        class="w-4 h-4 text-indigo-600">
                                    <div>
                                        <p class="font-medium text-gray-900">Relaxed</p>
                                        <p class="text-sm text-gray-600">Warnings only, child can continue using device</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer" :class="limits.mode === 'Strict' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                    <input 
                                        type="radio" 
                                        value="Strict" 
                                        x-model="limits.mode"
                                        class="w-4 h-4 text-indigo-600">
                                    <div>
                                        <p class="font-medium text-gray-900">Strict</p>
                                        <p class="text-sm text-gray-600">App automatically locks after daily limit is reached</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Distance Thresholds -->
                        <div class="border-b pb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Harmful Distance Threshold -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Harmful Distance Threshold: <span class="text-red-600 font-bold" x-text="limits.harmful_distance_threshold + ' cm'"></span>
                                    </label>
                                    <input 
                                        type="range" 
                                        min="1" 
                                        max="100" 
                                        step="1"
                                        x-model.number="limits.harmful_distance_threshold"
                                        class="w-full h-3 bg-red-200 rounded-lg appearance-none cursor-pointer accent-red-600">
                                    <p class="text-xs text-gray-500 mt-2">Distance closer than this will trigger warnings</p>
                                </div>

                                <!-- Critical Distance Threshold -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        Critical Distance Threshold: <span class="text-orange-600 font-bold" x-text="limits.critical_distance_threshold + ' cm'"></span>
                                    </label>
                                    <input 
                                        type="range" 
                                        min="1" 
                                        max="100" 
                                        step="1"
                                        x-model.number="limits.critical_distance_threshold"
                                        class="w-full h-3 bg-orange-200 rounded-lg appearance-none cursor-pointer accent-orange-600">
                                    <p class="text-xs text-gray-500 mt-2">Distance closer than this will lock the app</p>
                                </div>
                            </div>
                        </div>

                        <!-- Auto-Enforce Breaks Toggle -->
                        <div class="border-b pb-6">
                            <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Auto-Enforce 20-20-20 Breaks</p>
                                    <p class="text-sm text-gray-600 mt-1">Automatically pause the app every 20 minutes to encourage eye breaks</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        x-model="limits.auto_enforce_breaks"
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex gap-4">
                            <button 
                                type="submit"
                                :disabled="isSaving"
                                class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                x-text="isSaving ? 'Saving...' : 'Save Changes'">
                            </button>
                            <button 
                                type="button"
                                @click="resetLimits()"
                                class="px-6 py-3 bg-gray-200 text-gray-900 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                                Cancel
                            </button>
                        </div>

                        <!-- Success Message -->
                        <div x-show="showSuccessMessage" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-green-800 font-medium">✓ Changes saved successfully!</p>
                        </div>

                        <!-- Error Message -->
                        <div x-show="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-800 font-medium" x-text="'Error: ' + errorMessage"></p>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function guardianDashboard() {
            return {
                activeTab: 'overview',
                childName: 'Child Profile',
                eyeHealthScore: 78,
                todayScreenTime: 145,
                avgBlinkRate: 18,
                avgEyeDistance: 35,
                strainEventsToday: 2,
                breakCompliancePercent: 85,
                breaksCompleted: 17,
                breaksScheduled: 20,
                lastBreakTime: '2 minutes ago',
                nextBreakTime: '18 minutes from now',
                avgWeeklyScreenTime: 152,
                screenTimeTrend: 5,
                avgWeeklyBlinkRate: 17,
                blinkRateTrend: -3,
                avgWeeklyEyeDistance: 36,
                eyeDistanceTrend: 2,
                isSaving: false,
                showSuccessMessage: false,
                errorMessage: '',
                limits: {
                    daily_limit_minutes: 120,
                    mode: 'Relaxed',
                    harmful_distance_threshold: 30,
                    critical_distance_threshold: 10,
                    auto_enforce_breaks: true,
                    device_timestamp: new Date().toISOString().slice(0, 19).replace('T', ' ')
                },
                originalLimits: {},
                eyeHealthChart: null,
                trendChart: null,

                init() {
                    this.originalLimits = JSON.parse(JSON.stringify(this.limits));
                    this.$nextTick(() => {
                        this.initEyeHealthChart();
                        this.initTrendChart();
                        this.loadMetrics();
                    });
                },

                initEyeHealthChart() {
                    const ctx = document.getElementById('eyeHealthChart')?.getContext('2d');
                    if (!ctx) return;

                    this.eyeHealthChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Healthy', 'At Risk'],
                            datasets: [{
                                data: [this.eyeHealthScore, 100 - this.eyeHealthScore],
                                backgroundColor: ['#4F46E5', '#E5E7EB'],
                                borderWidth: 0,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        font: { size: 12 }
                                    }
                                }
                            }
                        }
                    });
                },

                initTrendChart() {
                    const ctx = document.getElementById('trendChart')?.getContext('2d');
                    if (!ctx) return;

                    this.trendChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                            datasets: [
                                {
                                    label: 'Eye Health Score',
                                    data: [75, 76, 78, 77, 79, 80, 78],
                                    borderColor: '#4F46E5',
                                    tension: 0.4,
                                    fill: false,
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#4F46E5'
                                },
                                {
                                    label: 'Break Compliance %',
                                    data: [80, 82, 85, 83, 87, 89, 85],
                                    borderColor: '#10B981',
                                    tension: 0.4,
                                    fill: false,
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#10B981'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    labels: {
                                        padding: 15,
                                        font: { size: 12 }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    title: { display: true, text: 'Score' }
                                }
                            }
                        }
                    });
                },

                loadMetrics() {
                    // In production, fetch from API
                    // For now, using mock data that updates dashboard
                },

                async updateLimits() {
                    this.isSaving = true;
                    this.showSuccessMessage = false;
                    this.errorMessage = '';

                    try {
                        const childId = new URLSearchParams(window.location.search).get('child_id') || 1;
                        const response = await fetch(`/api/mobile/child/${childId}/sync/limits`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': `Bearer ${this.getAuthToken()}`
                            },
                            body: JSON.stringify(this.limits)
                        });

                        const data = await response.json();

                        if (response.ok && data.status === 'success') {
                            this.showSuccessMessage = true;
                            this.originalLimits = JSON.parse(JSON.stringify(this.limits));
                            setTimeout(() => this.showSuccessMessage = false, 3000);
                        } else {
                            this.errorMessage = data.message || 'Failed to save changes';
                        }
                    } catch (error) {
                        this.errorMessage = error.message;
                    } finally {
                        this.isSaving = false;
                    }
                },

                resetLimits() {
                    this.limits = JSON.parse(JSON.stringify(this.originalLimits));
                    this.errorMessage = '';
                },

                getAuthToken() {
                    // In production, retrieve from secure storage
                    return localStorage.getItem('auth_token') || '';
                }
            };
        }
    </script>
</body>
</html>
