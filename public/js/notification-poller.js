/**
 * Enhanced Notification System
 * Real-time notifications with toast display and badge updates
 */
(function () {
  "use strict";

  const POLL_INTERVAL = 30000; // 30 seconds
  const COUNT_API_URL = "/modulo3/api/notifications/unread-count";
  const NOTIFICATIONS_API_URL = "/modulo3/api/notifications";

  let pollTimer = null;
  let lastNotificationCount = 0;
  let shownNotifications = new Set(); // Track shown notifications to avoid duplicates

  /**
   * Create and show a toast notification
   */
  function showToastNotification(notification) {
    // Check if we've already shown this notification
    const notificationKey = `${notification.id}-${notification.created_at}`;
    if (shownNotifications.has(notificationKey)) {
      return;
    }
    shownNotifications.add(notificationKey);

    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('notification-toast-container');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'notification-toast-container';
      toastContainer.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        max-width: 350px;
      `;
      document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = `toast-${Date.now()}`;
    const toastHtml = `
      <div id="${toastId}" class="toast show" role="alert" style="margin-bottom: 10px;">
        <div class="toast-header bg-${notification.color || 'info'} text-white">
          <i class="bi bi-bell-fill me-2"></i>
          <strong class="me-auto">${notification.titulo || 'Notificación'}</strong>
          <small>${notification.time_ago || 'Ahora'}</small>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
          ${notification.mensaje || ''}
        </div>
      </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
      if (toastElement) {
        toastElement.classList.remove('show');
        setTimeout(() => {
          if (toastElement) {
            toastElement.remove();
          }
        }, 300);
      }
    }, 5000);

    // Manual close button
    const closeBtn = toastElement.querySelector('.btn-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        toastElement.classList.remove('show');
        setTimeout(() => toastElement.remove(), 300);
      });
    }
  }

  /**
   * Update notification badge with count
   */
  function updateNotificationBadge(count) {
    // Find all notification badges (mobile and desktop)
    const badges = document.querySelectorAll(
      ".navbar .badge.bg-danger, .navbar .badge.rounded-pill.bg-danger"
    );

    badges.forEach((badge) => {
      if (count > 0) {
        badge.textContent = count;
        badge.style.display = "";

        // Add pulse animation for new notifications
        if (count > lastNotificationCount) {
          badge.classList.add("animate__animated", "animate__pulse");
          setTimeout(() => {
            badge.classList.remove("animate__animated", "animate__pulse");
          }, 1000);
        }
      } else {
        badge.style.display = "none";
      }
    });

    // Update page title with count
    updatePageTitle(count);
    lastNotificationCount = count;
  }

  /**
   * Update page title with notification count
   */
  function updatePageTitle(count) {
    const baseTitle = document.title.replace(/^\(\d+\)\s*/, "");
    if (count > 0) {
      document.title = `(${count}) ${baseTitle}`;
    } else {
      document.title = baseTitle;
    }
  }

  /**
   * Fetch unread notification count from API
   */
  async function fetchUnreadCount() {
    try {
      const response = await fetch(COUNT_API_URL, {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "same-origin",
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.success && typeof data.count === "number") {
        updateNotificationBadge(data.count);
        
        // If we have new notifications, fetch them to show toasts
        if (data.count > lastNotificationCount) {
          fetchRecentNotifications();
        }
      }
    } catch (error) {
      console.error("[Notifications] Error fetching unread count:", error);
    }
  }

  /**
   * Fetch recent notifications to show as toasts
   */
  async function fetchRecentNotifications() {
    try {
      const response = await fetch(`${NOTIFICATIONS_API_URL}?limit=5`, {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "same-origin",
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.success && data.data && Array.isArray(data.data)) {
        // Show only unread notifications as toasts
        data.data.forEach(notification => {
          if (!notification.is_leida || notification.is_leida === 0) {
            showToastNotification(notification);
          }
        });
      }
    } catch (error) {
      console.error("[Notifications] Error fetching recent notifications:", error);
    }
  }

  /**
   * Start polling for notifications
   */
  function startPolling() {
    // Initial fetch
    fetchUnreadCount();

    // Set up interval
    pollTimer = setInterval(fetchUnreadCount, POLL_INTERVAL);

    console.log("[Notifications] Enhanced polling started (every 30s)");
  }

  /**
   * Stop polling
   */
  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
      console.log("[Notifications] Polling stopped");
    }
  }

  /**
   * Handle visibility change (pause when tab is hidden)
   */
  function handleVisibilityChange() {
    if (document.hidden) {
      stopPolling();
    } else {
      startPolling();
      // When tab becomes visible again, check for new notifications immediately
      fetchUnreadCount();
    }
  }

  /**
   * Initialize polling system
   */
  function init() {
    // Start polling when page loads
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", startPolling);
    } else {
      startPolling();
    }

    // Pause polling when tab is hidden to save resources
    document.addEventListener("visibilitychange", handleVisibilityChange);

    // Cleanup on page unload
    window.addEventListener("beforeunload", stopPolling);

    // Listen for custom events to trigger notification refresh
    document.addEventListener('refreshNotifications', () => {
      fetchUnreadCount();
    });
  }

  // Initialize
  init();

  // Expose API for manual control
  window.NotificationPoller = {
    start: startPolling,
    stop: stopPolling,
    refresh: fetchUnreadCount,
    showToast: showToastNotification
  };
})();
