<?php
/**
 * Template Name: test server
 * Тестовый файл для проверки service.php
 * Положите в корень сайта: /test-service.php
 * Откройте: https://wptest.local/test-service.php
 */

// Загружаем WordPress
require_once('wp-load.php');

echo "<h1>CDEK Service Test</h1>";

// Проверяем настройки
$dev_mode = get_option('cdek_dev_mode', 'no');
echo "<h2>Settings:</h2>";
echo "cdek_dev_mode: <strong>" . $dev_mode . "</strong><br>";
echo "cdek_account: " . (get_option('cdek_account') ? 'SET' : 'EMPTY') . "<br>";
echo "cdek_test_mode: " . get_option('cdek_test_mode', 'no') . "<br>";

// Проверяем путь к mock-data.php
$mock_file = WP_CONTENT_DIR . '/plugins/cdek-simple/assets/cdek-widget/mock-data.php';
echo "<h2>Mock Data File:</h2>";
echo "Path: " . $mock_file . "<br>";
echo "Exists: " . (file_exists($mock_file) ? '✅ YES' : '❌ NO') . "<br>";

if (file_exists($mock_file)) {
    $mock_data = include($mock_file);
    echo "Mock offices count: <strong>" . count($mock_data) . "</strong><br>";
    echo "<h3>Mock Data Preview:</h3>";
    echo "<pre>" . print_r($mock_data, true) . "</pre>";
}

// Проверяем service.php
$service_file = WP_CONTENT_DIR . '/plugins/cdek-simple/assets/cdek-widget/service.php';
echo "<h2>Service File:</h2>";
echo "Path: " . $service_file . "<br>";
echo "Exists: " . (file_exists($service_file) ? '✅ YES' : '❌ NO') . "<br>";

// Тестируем прямой запрос
echo "<h2>Direct Test:</h2>";
echo "<a href='wp-content/plugins/cdek-simple/assets/cdek-widget/service.php?action=offices&debug=1' target='_blank'>Test service.php</a>";