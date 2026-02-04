<?php
session_start();
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

require_once '../config/Database.php';
require_once BASE_PATH . '/config/config.php';

require_once '../public/home.php';
require_once '../routes.php';
