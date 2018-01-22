<?php

// подключаем настройки и класс
require_once 'api_settings.php';
require_once 'articlesAPI.php';

// выполняем
$api = new articlesAPI();
$result = $api->execute();

// отправляем результат
echo json_encode($result, JSON_UNESCAPED_UNICODE);