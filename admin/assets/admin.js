// Admin Dashboard JavaScript - The Stitch House

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize dashboard
    initializeDashboard();
    
    // Initialize sidebar interactions
    initializeSidebar();
    
    // Initialize responsive features
    initializeResponsive();
    
    // Initialize tooltips
    initializeTooltips();
});

/**
 * Initialize dashboard functionality
 */
function initializeDashboard() {
    // Animate cards on load
    animateElements();
    
    // Initialize chart period selector
    const chartPeriodSelect = document.getElementById('chartPeriod');
    if (chartPeriodSelect) {
        chartPeriodSelect.addEventListener('change', function() {
            updateFinancialChart(this.value);
        });
    }
    
    // Auto-refresh dashboard data every 5 minutes
    setInterval(refreshDashboardData, 300000);
    
    // Initialize real-time updates
    initializeRealTimeUpdates();
}

/**
 * Initialize sidebar interactions
 */
function initializeSidebar() {
    // Handle dropdown toggles
    const dropdownToggles = document.querySelectorAll('.nav-link.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('data-bs-target');
            const target = document.querySelector(targetId);
            
            if (target) {
                // Close other dropdowns
                const allDropdowns = document.querySelectorAll('.sidebar-menu .collapse');
                allDropdowns.forEach(dropdown => {
                    if (dropdown !== target && dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                target.classList.toggle('show');
                
                // Update icon
                const icon = this.querySelector('.fa-plus');
                if (icon) {
                    icon.classList.toggle('fa-plus');
                    icon.classList.toggle('fa-minus');
                }
            }
        });
    });
    
    // Highlight active menu item
    highlightActiveMenuItem();
}

/**
 * Initialize responsive features
 */
function initializeResponsive() {
    // Mobile sidebar toggle
    const sidebarToggle = document.createElement('button');
    sidebarToggle.className = 'btn btn-primary d-lg-none position-fixed';
    sidebarToggle.style.cssText = 'top: 1rem; left: 1rem; z-index: 1100;';
    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
    
    document.body.appendChild(sidebarToggle);
    
    sidebarToggle.addEventListener('click', function() {
        const sidebar = document.querySelector('.admin-sidebar');
        sidebar.classList.toggle('show');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.admin-sidebar');
        const sidebarToggle = e.target.closest('button');
        
        if (window.innerWidth < 992 && 
            !sidebar.contains(e.target) && 
            !sidebarToggle && 
            sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
    });
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Animate elements on page load
 */
function animateElements() {
    // Animate stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 100);
    });
    
    // Animate other cards
    const otherCards = document.querySelectorAll('.chart-card, .activity-card');
    otherCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('slide-in-left');
        }, (statCards.length * 100) + (index * 150));
    });
}

/**
 * Highlight active menu item
 */
function highlightActiveMenuItem() {
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.sidebar-menu .nav-link');
    
    menuLinks.forEach(link => {
        link.classList.remove('active');
        
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) {
            link.classList.add('active');
            
            // Open parent dropdown if exists
            const parentCollapse = link.closest('.collapse');
            if (parentCollapse) {
                parentCollapse.classList.add('show');
                const parentToggle = document.querySelector(`[data-bs-target="#${parentCollapse.id}"]`);
                if (parentToggle) {
                    const icon = parentToggle.querySelector('.fa-plus');
                    if (icon) {
                        icon.classList.remove('fa-plus');
                        icon.classList.add('fa-minus');
                    }
                }
            }
        }
    });
}

/**
 * Update financial chart with new period
 */
function updateFinancialChart(period) {
    // Show loading spinner
    const chartContainer = document.querySelector('.chart-body');
    const originalContent = chartContainer.innerHTML;
    
    chartContainer.innerHTML = `
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Simulate API call
    fetch(`get-chart-data.php?period=${period}`)
        .then(response => response.json())
        .then(data => {
            // Restore chart container
            chartContainer.innerHTML = '<canvas id="financialChart" height="100"></canvas>';
            
            // Recreate chart with new data
            createFinancialChart(data);
        })
        .catch(error => {
            console.error('Error updating chart:', error);
            chartContainer.innerHTML = originalContent;
            showNotification('Error updating chart data', 'error');
        });
}

/**
 * Create financial chart
 */
function createFinancialChart(data) {
    const ctx = document.getElementById('financialChart');
    if (!ctx) return;
    
    // Destroy existing chart if exists
    if (window.financialChartInstance) {
        window.financialChartInstance.destroy();
    }
    
    // Process data for chart
    const labels = [];
    const incomeData = [];
    const expenseData = [];
    
    // Create date range for the chart
    const today = new Date();
    const period = document.getElementById('chartPeriod')?.value || 30;
    
    for (let i = period - 1; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        
        // Find matching data
        const dateStr = date.toISOString().split('T')[0];
        const incomeItem = data.income?.find(item => item.date === dateStr);
        const expenseItem = data.expenses?.find(item => item.date === dateStr);
        
        incomeData.push(incomeItem ? parseFloat(incomeItem.amount) : 0);
        expenseData.push(expenseItem ? parseFloat(expenseItem.amount) : 0);
    }
    
    window.financialChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Income',
                data: incomeData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Expenses',
                data: expenseData,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    });
}

/**
 * Refresh dashboard data
 */
function refreshDashboardData() {
    fetch('get-dashboard-stats.php')
        .then(response => response.json())
        .then(data => {
            updateDashboardStats(data);
        })
        .catch(error => {
            console.error('Error refreshing dashboard data:', error);
        });
}

/**
 * Update dashboard statistics
 */
function updateDashboardStats(data) {
    // Update stat numbers with animation
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach((element, index) => {
        const targetValue = Object.values(data)[index];
        if (targetValue !== undefined) {
            animateNumber(element, parseFloat(element.textContent.replace(/[^0-9.-]+/g, "")), targetValue);
        }
    });
}

/**
 * Animate number changes
 */
function animateNumber(element, start, end, duration = 1000) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        
        // Format number based on context
        if (element.closest('.stat-card-warning')) {
            element.textContent = '$' + Math.round(current).toLocaleString();
        } else {
            element.textContent = Math.round(current).toLocaleString();
        }
    }, 16);
}

/**
 * Initialize real-time updates
 */
function initializeRealTimeUpdates() {
    // Update time every minute
    setInterval(() => {
        const timeElements = document.querySelectorAll('.time-display');
        timeElements.forEach(element => {
            element.textContent = new Date().toLocaleTimeString();
        });
    }, 60000);
    
    // Check for new notifications every 2 minutes
    setInterval(checkNewNotifications, 120000);
}

/**
 * Check for new notifications
 */
function checkNewNotifications() {
    fetch('get-notifications.php')
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.count);
            
            // Show new notifications
            if (data.new && data.new.length > 0) {
                data.new.forEach(notification => {
                    showNotification(notification.message, notification.type);
                });
            }
        })
        .catch(error => {
            console.error('Error checking notifications:', error);
        });
}

/**
 * Update notification badge
 */
function updateNotificationBadge(count) {
    const badge = document.querySelector('.header-actions .badge');
    if (badge) {
        badge.textContent = count;
        if (count > 0) {
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }
    }
}

/**
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Add to toast container or create one
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.appendChild(toast);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove from DOM after hiding
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

/**
 * Utility functions
 */

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export functions for global use
window.AdminDashboard = {
    updateFinancialChart,
    showNotification,
    refreshDashboardData,
    formatCurrency,
    formatDate
};