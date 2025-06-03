<?php
/**
 * Controller: /assets/save_ass
 * Method: post
 * File: save_ass.php
 */

// Boot Kernel if needed
if (!class_exists(\App\Core\AppKernel::class)) {
    require_once dirname(__DIR__, 4) . '/app/core/AppKernel.php';
    \App\Core\AppKernel::boot();
}

use App\Core\AppKernel;
use App\Helpers\Core\FlashHelper;
use App\Core\App;

try {
    // Add your controller logic here
    
    FlashHelper::set('success', 'Operation completed successfully');
} catch (Throwable $e) {
    FlashHelper::set('error', $e->getMessage());
}

App::redirect(BASE_URL . '/assets/save_ass');