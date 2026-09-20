<?php

// Fix SCRIPT_NAME for Vercel serverless environment so /api/* routes map to routes/api.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

require __DIR__ . '/../public/index.php';