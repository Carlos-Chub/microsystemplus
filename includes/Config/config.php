<?php
// include  __DIR__ . '/fastandfurious.php';
require_once(__DIR__ . '/../../vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
$host = $_ENV['HOST'];
define('BASE_URL', $host);
