/**
 * layout.core.js
 * Global UI and layout enhancements for Tabler-based admin panel
 * - Sidebar search & state memory
 * - Toast auto-show
 */

// 🔍 Filter sidebar items based on query
window.filterSidebarMenu = function(query) {
  const navItems = document.querySelectorAll("#sidebar-nav .nav-item");
  query = query.toLowerCase();
  navItems.forEach(item => {
    const text = item.innerText.toLowerCase();
    item.style.display = text.includes(query) ? "" : "none";
  });
};

// 🚀 DOM Ready: Sidebar state & toast logic
document.addEventListener("DOMContentLoaded", function () {
  const sidebarNav = document.getElementById("sidebar-nav");

  // 🌟 Restore sidebar open state
  if (sidebarNav) {
    const openMenuId = sessionStorage.getItem("openSidebarMenu");
    if (openMenuId) {
      const collapse = document.getElementById(openMenuId);
      if (collapse) {
        collapse.classList.add("show");
        const toggle = document.querySelector(`[href="#${openMenuId}"]`);
        if (toggle) toggle.setAttribute("aria-expanded", "true");
      }
    }

    // 💾 Save expanded/collapsed menus
    sidebarNav.querySelectorAll('[data-bs-toggle="collapse"]').forEach(toggle => {
      toggle.addEventListener("click", function () {
        const targetId = toggle.getAttribute("href")?.replace("#", "");
        const target = document.getElementById(targetId);
        if (!target) return;
        if (!target.classList.contains("show")) {
          sessionStorage.setItem("openSidebarMenu", targetId);
        } else {
          sessionStorage.removeItem("openSidebarMenu");
        }
      });
    });
  }

  // 🔔 Automatically show any Bootstrap toasts
  document.querySelectorAll('.toast').forEach(toast => {
    const instance = bootstrap.Toast.getOrCreateInstance(toast);
    instance.show();
  });
});
