1) API понимает и GET, и POST запросы

2) Ответы приходят в формате объекта с двумя полями.
    первое поле - `completed` - содержит true (успешное выполнение) или false (ошибка)
    в случае ошибки второе поле называется `message` и содержит описание ошибки,
    в случае успешного выполнения второе поле называется `result` и содержит список (массив)
    статей в виде объектов с полями:
        id - id статьи в базе данных
        author - автор
        category - категория
        tags - список (массив) тегов
        date - дата создания статьи в формате 2018-01-21
        name - название статьи
        description - краткое описание
        content - содержимое статьи

3) Тип запроса указывается с помощью параметра type.
    API понимает следующие типы:
        id - список из 0 или 1 статьи с данным id. Запрос - api.php?type=id&id=23
        category - список статей данной категории - api.php?type=category&category=development
        author - список статей данного автора - api.php?type=author&firstname=henry&lastname=miller
        tag - список статей с указанным тегом - api.php?type=tag&tag=middle
        day - список статей за этот день - api.php?type=day

4) API состоит из четырёх файлов:
    api_settings.php - настройки для хоста, имени базы данных, имени пользователя и пароля
    api_create_table.php - однократный запуск этого скрипта создаст в базе данных пять необходимых таблиц
    articlesAPI.php - собственно, само API, оформленное в виде класса.
    api.php - на этот файл следует слать запросы.

5) Таблицы:
    tags - теги - поля id и name
    authors - авторы - id, firstname, lastname
    categories - категории - id, name
    articles - статьи - id, author_id, category_id, name (название),
        description (описание), content (содержимое), date (дата создания в формате 2018-01-22)
    relations - пары статья-тег. поля id, id_tag, id_article