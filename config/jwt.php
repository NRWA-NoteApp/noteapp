<?php

return [
    'secret' => getenv('JWT_SECRET') ?: 'noteapp-local-development-secret',
    'expires_in' => 60 * 60 * 24,
];
