<?php

// подключаем настройки
require_once 'api_settings.php';

// просто вспомогательная функция
// выводит в браузер результаты
function helpShow($res, $tablename)
{
    $message = $res === false ?
        "<pre style = 'color: red'>таблица {$tablename} не создана!</pre>"
        : "<pre style = 'color: green'>таблица {$tablename} успешно создана</pre>";

    echo $message;
}

// подключаемся к базе данных
try {
    $pdo = new PDO(
        'mysql:host='.API_DB_HOSTNAME.';dbname='.API_DB_DATABASE.';charset=utf8',
        API_DB_USERNAME,
        API_DB_PASSWORD
    );
} catch (PDOException $e) {
    exit('Невозможно установить соединение с сервером mysql');
}

// создаём пять таблиц

$res = $pdo->exec(
    'CREATE TABLE tags (
id INT(11) NOT NULL AUTO_INCREMENT,
name TINYTEXT,
PRIMARY KEY (id)
)'
);
helpShow($res, 'tags');

$res = $pdo->exec(
    'CREATE TABLE authors (
id INT(11) NOT NULL AUTO_INCREMENT,
firstname TINYTEXT,
lastname TINYTEXT,
PRIMARY KEY (id)
)'
);
helpShow($res, 'authors');

$res = $pdo->exec(
    'CREATE TABLE categories (
id INT(11) NOT NULL AUTO_INCREMENT,
name TINYTEXT,
PRIMARY KEY (id)
)'
);
helpShow($res, 'categories');

$res = $pdo->exec(
    'CREATE TABLE relations (
id INT(11) NOT NULL AUTO_INCREMENT,
id_tag INT(11),
id_article INT(11),
PRIMARY KEY (id)
)'
);
helpShow($res, 'relations');

$res = $pdo->exec(
    'CREATE TABLE articles (
id INT(11) NOT NULL AUTO_INCREMENT,
author_id INT(11),
category_id INT(11),
name TINYTEXT,
description TEXT,
content TEXT,
date DATE,
PRIMARY KEY (id)
)'
);
helpShow($res, 'articles');