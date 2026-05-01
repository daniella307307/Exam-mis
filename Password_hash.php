<?php
/**
 * NOTE: This file used to be a public endpoint that, when hit, would
 * insert a user with the hardcoded password "123456". That is a
 * production backdoor. The behaviour has been removed.
 *
 * If you ever need to seed an account from the CLI, run a real
 * migration or use the registration flow. This script now refuses
 * to do anything when accessed via the web.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(410);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Gone. This endpoint has been removed for security reasons.\n";
    exit;
}

fwrite(STDERR, "This file no longer creates accounts. Use the registration flow.\n");
exit(1);
