<!-- Pending Patient Requests Panel -->
<div class="card mb-4">
    <div class="card-header bg-white border-bottom p-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title fw-bold mb-1">
                    <i class="bi bi-inbox"></i> Incoming Patient Requests
                </h5>
                <p class="text-muted small mb-0">Pending requests from guardians to assign their children</p>
            </div>
            <div class="badge bg-primary rounded-pill fs-6" id="requestBadge" style="display: none;">
                <span id="requestCount">0</span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div id="requestsContainer">
            <!-- Loading State -->
            <div id="loadingState" class="p-4 text-center">
                <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2">Loading requests...</p>
            </div>

            <!-- Empty State -->
            <div id="emptyState" style="display: none; padding: 3rem 2rem;">
                <div class="text-center">
                    <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <p class="text-muted mb-0">No pending requests at the moment</p>
                </div>
            </div>

            <!-- Requests List -->
            <div id="requestsList">
                <!-- Each request will be inserted here -->
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer" style="position: fixed; top: 80px; right: 20px; z-index: 1050; width: 400px; max-width: 95vw;">
            <!-- Alerts will be inserted here -->
        </div>
    </div>
</div>

<script>
// Get the doctor's ID from the page (set in the doctor controller)
const doctorId = "{{ $doctorProfile->doctor_id ?? '' }}";
const authToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

/**
 * Fetch pending requests for this doctor
 */
async function loadPendingRequests() {
    try {
        const response = await fetch(`/api/web/doctor/${doctorId}/requests`, {
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': authToken,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            console.error('Failed to load requests:', response.status);
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('emptyState').style.display = 'none';
            showAlert('error', `Unable to load incoming requests (HTTP ${response.status}). Please refresh or sign in again.`);
            return;
        }

        const data = await response.json();
        renderRequests(data.requests || []);
    } catch (error) {
        console.error('Error loading pending requests:', error);
        document.getElementById('loadingState').style.display = 'none';
        showAlert('error', 'Failed to load requests. Please try again later.');
    }
}

/**
 * Render the list of pending requests
 */
function renderRequests(requests) {
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const requestsList = document.getElementById('requestsList');
    const badgeElement = document.getElementById('requestBadge');
    const requestCount = document.getElementById('requestCount');

    // Hide loading state
    loadingState.style.display = 'none';

    if (requests.length === 0) {
        emptyState.style.display = 'block';
        requestsList.innerHTML = '';
        badgeElement.style.display = 'none';
    } else {
        emptyState.style.display = 'none';
        badgeElement.style.display = 'inline-block';
        requestCount.textContent = requests.length;

        requestsList.innerHTML = requests.map(req => `
            <div class="request-item border-bottom p-4" data-link-id="${req.link_id}" data-status="pending">
                <div class="d-flex gap-3">
                    <!-- Patient Avatar -->
                    <div class="avatar-placeholder" style="
                        width: 48px;
                        height: 48px;
                        background-color: #527267;
                        color: white;
                        border-radius: 0.5rem;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: 600;
                        flex-shrink: 0;
                    ">
                        ${req.child_name.charAt(0).toUpperCase()}
                    </div>

                    <!-- Request Details -->
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0 fw-semibold">${req.child_name}</h6>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-envelope"></i>
                                    ${req.child_email}
                                </p>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-calendar-event"></i>
                                    Requested on ${new Date(req.request_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}
                                </p>
                            </div>
                            <span class="badge bg-warning text-dark">Pending</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 mt-3">
                            <button 
                                class="btn btn-sm btn-success accept-btn" 
                                onclick="handleRequestAction(${req.link_id}, 'accept', this)"
                                style="min-width: 100px;"
                            >
                                <i class="bi bi-check-circle me-1"></i> Accept
                            </button>
                            <button 
                                class="btn btn-sm btn-danger deny-btn" 
                                onclick="handleRequestAction(${req.link_id}, 'deny', this)"
                                style="min-width: 100px;"
                            >
                                <i class="bi bi-x-circle me-1"></i> Deny
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
}

/**
 * Handle Accept/Deny actions
 */
async function handleRequestAction(linkId, action, buttonElement) {
    // Disable button and show loading state
    buttonElement.disabled = true;
    const originalText = buttonElement.innerHTML;
    buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';

    try {
        const response = await fetch(`/api/web/doctor/requests/${linkId}`, {
            credentials: 'same-origin',
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': authToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ action: action })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        // Remove the request from the DOM with animation
        const requestItem = document.querySelector(`[data-link-id="${linkId}"]`);
        requestItem.style.opacity = '0';
        requestItem.style.transform = 'translateX(20px)';
        requestItem.style.transition = 'all 0.3s ease-out';

        setTimeout(() => {
            requestItem.remove();
            updateRequestsDisplay();

            // Show success message
            const actionText = action === 'accept' ? 'accepted' : 'denied';
            showAlert('success', `Patient request ${actionText} successfully!`);
        }, 300);

    } catch (error) {
        console.error('Error handling request:', error);
        buttonElement.disabled = false;
        buttonElement.innerHTML = originalText;
        showAlert('error', `Failed to ${action} request. Please try again.`);
    }
}

/**
 * Update the display based on remaining requests
 */
function updateRequestsDisplay() {
    const requestsList = document.getElementById('requestsList');
    const remainingRequests = requestsList.querySelectorAll('.request-item').length;
    const badgeElement = document.getElementById('requestBadge');
    const emptyState = document.getElementById('emptyState');

    if (remainingRequests === 0) {
        requestsList.innerHTML = '';
        emptyState.style.display = 'block';
        badgeElement.style.display = 'none';
    } else {
        document.getElementById('requestCount').textContent = remainingRequests;
    }
}

/**
 * Show alert messages
 */
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    const alertId = `alert-${Date.now()}`;
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';

    const alertElement = document.createElement('div');
    alertElement.id = alertId;
    alertElement.className = `alert ${alertClass} alert-dismissible fade show`;
    alertElement.setAttribute('role', 'alert');
    alertElement.innerHTML = `
        <strong>${type === 'success' ? '✓ Success!' : type === 'error' ? '✗ Error!' : 'ℹ Info'}</strong>
        <br>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    alertContainer.appendChild(alertElement);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease-out';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
}

/**
 * Refresh requests periodically or on demand
 */
function refreshRequests() {
    loadPendingRequests();
}

// Load requests when panel is initialized
document.addEventListener('DOMContentLoaded', function() {
    loadPendingRequests();

    // Optional: Refresh every 30 seconds
    setInterval(refreshRequests, 30000);
});
</script>

<style>
.request-item {
    transition: all 0.2s ease-in-out;
}

.request-item:hover {
    background-color: #f8fafc;
}

.request-item:last-child {
    border-bottom: none !important;
}

.accept-btn, .deny-btn {
    transition: all 0.2s ease-in-out;
    font-size: 0.875rem;
}

.accept-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
}

.deny-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
}

.accept-btn:disabled, .deny-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

#requestBadge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

#alertContainer .alert {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
