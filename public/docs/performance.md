# 🚀 Performance Monitoring Guide

EGS-ADMIN includes a full performance monitoring system for both backend (PHP) and frontend (JavaScript) operations.

---

## 🖥️ Backend Monitoring (PHP)
- Tracks total execution time, memory usage, and peak memory
- Monitors loop iterations, timers, and memory snapshots
- Shows top 10 included files by size
- Warnings for slow operations or high memory usage

### Usage
- Metrics are shown on `/system/performance`
- Use `PerformanceMonitor::startTimer('name')` and `endTimer('name')` to track custom code blocks
- Use `PerformanceMonitor::trackLoop('loopName')` to monitor loops

---

## 🌐 Frontend Monitoring (JS)
- Tracks page load time, memory usage, AJAX calls
- Shows warnings for slow or memory-heavy operations
- Use `performanceMonitor.startTimer('name')` and `endTimer('name')` in JS
- Metrics auto-refresh every 5 seconds

---

## 📝 Best Practices
- Use timers for any heavy or critical code
- Watch for warnings about slow or memory-heavy operations
- Use the performance page to identify bottlenecks

---

## ⚠️ Troubleshooting
- If numbers are static, try adding test code or interacting with the page
- For live backend stats, implement an AJAX endpoint to fetch `getSummary()`
- Use Chrome for best frontend memory stats 