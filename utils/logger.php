<?php
/**
 * Simple logger utility for error reporting.
 * Writes exception messages and stack traces to a log file in `/tmp`.
 * In production you may want to route this to a proper logging system.
 */
function logError(Throwable $e): void
{
    $logFile = __DIR__ . '/../logs/error.log';
    $msg = sprintf("[%s] %s in %s on line %d\nStack trace:\n%s\n\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    // Ensure logs directory exists
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, $msg, FILE_APPEND);
}
?>
