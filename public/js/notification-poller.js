/**
 * Notification Polling System
 * Auto-updates notification badge count every 30 seconds
 */
(function () {
  "use strict";

  const POLL_INTERVAL = 30000; // 30 seconds
  const API_URL = "/modulo3/api/notifications/unread-count";

  let pollTimer = null;

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
        badge.classList.add("animate__animated", "animate__pulse");
        setTimeout(() => {
          badge.classList.remove("animate__animated", "animate__pulse");
        }, 1000);
      } else {
        badge.style.display = "none";
      }
    });

    // Update page title with count
    updatePageTitle(count);
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
      const response = await fetch(API_URL, {
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
      }
    } catch (error) {
      console.error("[Notifications] Error fetching unread count:", error);
      // Don't stop polling on error, just log it
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

    console.log("[Notifications] Polling started (every 30s)");
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
  }

  // Initialize
  init();

  // Expose API for manual control (optional)
  window.NotificationPoller = {
    start: startPolling,
    stop: stopPolling,
    refresh: fetchUnreadCount,
  };
})();
