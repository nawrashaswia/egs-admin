<?php

return [
    // System-level name for session cookie
    'session_name' => 'EGADMIN_SESSID',

    // Timeout in seconds (for inactivity auto-logout)
    'timeout' => 3600, // 1 hour

    // Automatically start session in AppKernel
    'auto_start' => true,

    // Optional: Regenerate ID on login to prevent fixation
    'regenerate_on_login' => true,

    // Optional: Enable secure cookie flags (for HTTPS)
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax', // 'Strict' or 'None' for cross-domain needs

    // Optional: Define known roles (for validation or UI)
    'roles' => ['admin', 'moderator', 'staff', 'viewer']
];
