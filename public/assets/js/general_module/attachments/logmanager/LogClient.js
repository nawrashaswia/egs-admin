/**
 * File: /public/assets/js/general_module/logmanager/LogClient.js
 */
const LogClient = {
    traceId: document.querySelector('meta[name="trace-id"]')?.content || window.TRACE_ID || null,

    trigger: function(event, context = {}, level = 'INFO', mode = 'js') {
        if (!this.traceId) return;

        fetch('/ajax/general_module/logger_receiver.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                trace_id: this.traceId,
                event,
                level,
                mode,
                context
            })
        }).catch(err => console.error('LogClient error:', err));
    }
};
