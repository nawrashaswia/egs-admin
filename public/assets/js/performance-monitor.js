class PerformanceMonitor {
    constructor() {
        this.timers = new Map();
        this.counters = new Map();
        this.warnings = [];
        this.startTime = performance.now();
        this.observers = new Map();
        this.loopCounters = new Map();
    }

    startTimer(name) {
        this.timers.set(name, {
            start: performance.now(),
            memory: performance.memory ? performance.memory.usedJSHeapSize : 0
        });
    }

    endTimer(name) {
        const timer = this.timers.get(name);
        if (!timer) return { error: 'Timer not found' };

        const endTime = performance.now();
        const duration = endTime - timer.start;
        const memoryUsed = performance.memory ? 
            performance.memory.usedJSHeapSize - timer.memory : 0;

        // Check for potential issues
        if (duration > 1000) { // More than 1 second
            this.warnings.push(`Timer '${name}' took ${duration.toFixed(2)}ms to complete`);
        }
        if (memoryUsed > 5 * 1024 * 1024) { // More than 5MB
            this.warnings.push(`Timer '${name}' used ${(memoryUsed / 1024 / 1024).toFixed(2)}MB of memory`);
        }

        return {
            duration,
            memoryUsed,
            warnings: this.warnings
        };
    }

    trackLoop(name, maxIterations = 1000) {
        const count = (this.loopCounters.get(name) || 0) + 1;
        this.loopCounters.set(name, count);

        if (count > maxIterations) {
            this.warnings.push(`Loop '${name}' exceeded ${maxIterations} iterations`);
            console.warn(`Potential infinite loop detected in '${name}'`);
            return false;
        }
        return true;
    }

    observeElement(selector, callback) {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    callback(mutation);
                }
            });
        });

        const element = document.querySelector(selector);
        if (element) {
            observer.observe(element, { childList: true, subtree: true });
            this.observers.set(selector, observer);
        }
    }

    stopObserving(selector) {
        const observer = this.observers.get(selector);
        if (observer) {
            observer.disconnect();
            this.observers.delete(selector);
        }
    }

    incrementCounter(name, amount = 1) {
        const count = (this.counters.get(name) || 0) + amount;
        this.counters.set(name, count);
        return count;
    }

    getCounter(name) {
        return this.counters.get(name) || 0;
    }

    getWarnings() {
        return this.warnings;
    }

    getSummary() {
        const endTime = performance.now();
        return {
            totalTime: endTime - this.startTime,
            memory: performance.memory ? {
                used: performance.memory.usedJSHeapSize,
                total: performance.memory.totalJSHeapSize,
                limit: performance.memory.jsHeapSizeLimit
            } : null,
            warnings: this.warnings,
            counters: Object.fromEntries(this.counters),
            loopCounts: Object.fromEntries(this.loopCounters)
        };
    }

    reset() {
        this.timers.clear();
        this.counters.clear();
        this.warnings = [];
        this.startTime = performance.now();
        this.loopCounters.clear();
        this.observers.forEach(observer => observer.disconnect());
        this.observers.clear();
    }
}

// Create global instance
window.performanceMonitor = new PerformanceMonitor();

// Example usage:
/*
// Monitor a function
performanceMonitor.startTimer('myFunction');
myFunction();
const result = performanceMonitor.endTimer('myFunction');
console.log(result);

// Monitor a loop
for (let i = 0; i < 1000; i++) {
    if (!performanceMonitor.trackLoop('myLoop')) {
        break; // Stop if too many iterations
    }
    // ... loop code
}

// Monitor DOM changes
performanceMonitor.observeElement('#myElement', (mutation) => {
    console.log('DOM changed:', mutation);
});

// Track counter
performanceMonitor.incrementCounter('apiCalls');
*/ 