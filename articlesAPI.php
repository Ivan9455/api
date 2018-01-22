<?php

class articlesAPI
{
    // тут лежит дескриптор подключения к базе данных
    private $pdo;

    // подключаемся к базе данных
    public function __construct()
    {
        $this->pdo = new PDO(
            'mysql:host='.API_DB_HOSTNAME.';dbname='.API_DB_DATABASE.';charset=utf8',
            API_DB_USERNAME,
            API_DB_PASSWORD
        );
    }

    // проверяем значение `type` и запускаем соответствующую функцию
    public function execute()
    {
        $type = $_REQUEST['type'] ?? false;

        if (!$type) return self::getErrorResponse('необходим параметр type');

        if ($type === 'author') {
            return $this->author();
        } elseif ($type === 'day') {
            return $this->day();
        } elseif ($type === 'tag') {
            return $this->tag();
        } elseif ($type === 'id') {
            return $this->id();
        } elseif ($type === 'category') {
            return $this->category();
        } else {
            return self::getErrorResponse('параметр type не является корректным');
        }
    }

    private function author()
    {
        // проверяем наличие параметра `firstname`
        $firstname = $_REQUEST['firstname'] ?? false;
        if (!$firstname) return self::getErrorResponse('отсутствует параметр firstname');

        // проверяем наличие параметра `lastname`
        $lastname = $_REQUEST['lastname'] ?? false;
        if (!$lastname) return self::getErrorResponse('отсутствует параметр lastname');

        // получаем id автора
        $res = $this->pdo->prepare('SELECT id FROM authors WHERE firstname = ? AND lastname = ?');
        $res->execute([$firstname, $lastname]);

        $res = $res->fetch(PDO::FETCH_ASSOC);
        if (!$res) return self::getErrorResponse("автор '{$firstname} {$lastname}' отсутствует");

        $id = $res['id'];

        //ищем статьи этого автора
        $articles = $this->pdo
            ->query(self::getQuery("author_id = {$id}"))
            ->fetchAll(PDO::FETCH_ASSOC);

        //добавляем информацию о тегах
        $this->addTags($articles);

        return self::getSuccessResponse($articles);
    }

    private function day()
    {
        $articles = $this->pdo
            ->query(self::getQuery("date = '".date('Y-m-d')."'"))
            ->fetchAll(PDO::FETCH_ASSOC);

        $this->addTags($articles);

        return self::getSuccessResponse($articles);
    }

    private function tag()
    {
        // проверяем наличие параметра `tag`
        $tag = $_REQUEST['tag'] ?? false;
        if (!$tag) return self::getErrorResponse('отсутствует параметр tag');

        // получаем id
        $res = $this->pdo
            ->prepare('SELECT id FROM tags WHERE name = ?');
        $res->execute([$tag]);
        $res = $res->fetch(PDO::FETCH_ASSOC);

        if (!$res) return self::getErrorResponse("тег '{$tag}' отсутствует");
        $id = $res['id'];

        // получаем список статей
        $articles = $this->pdo
            ->query(self::getQuery("relations.id_tag = {$id} AND relations.id_article = articles.id", ", relations"))
            ->fetchAll(PDO::FETCH_ASSOC);

        $this->addTags($articles);

        return self::getSuccessResponse($articles);
    }

    private function id()
    {
        $id = $_REQUEST['id'] ?? false;
        if (!$id) return self::getErrorResponse('необходим параметр id');

        $res = $this->pdo
            ->prepare(self::getQuery("articles.id = {$id}"));
        $res->execute([$id]);

        $articles = $res->fetchAll(PDO::FETCH_ASSOC);

        $this->addTags($articles);

        return self::getSuccessResponse($articles);
    }

    private function category()
    {
        $category = $_REQUEST['category'] ?? false;
        if (!$category) return self::getErrorResponse('отсутсвует параметр category');

        $res = $this->pdo
            ->prepare('SELECT id FROM categories WHERE name = ?');
        $res->execute([$category]);
        $res = $res->fetch(PDO::FETCH_ASSOC);

        if (!$res) return self::getErrorResponse("категория '{$category}' отсутствует");
        $id = $res['id'];

        $articles = $this->pdo
            ->query(self::getQuery("category_id = {$id}"))
            ->fetchAll(PDO::FETCH_ASSOC);

        $this->addTags($articles);

        return self::getSuccessResponse($articles);
    }

    // ниже - вспомогательные функции

    // добавляем информацию о тегах в список статей
    private function addTags(&$articles)
    {
        foreach ($articles as &$article)
        {
            $id = $article['id'];
            $tags = $this->pdo
                ->query("SELECT tags.name AS tag FROM tags, relations WHERE id_article = {$id} AND id_tag = tags.id")
                ->fetchAll(PDO::FETCH_ASSOC);

            $article['tags'] = array_map(
                function ($el)
                {
                    return $el['tag'];
                },
                $tags
            );
        }
    }

    // обёртка для запроса - позволяет получить не только данных из таблицы статей,
    // но и название категории, автора и т.д.
    private static function getQuery($rule, $tables = '')
    {
        return "SELECT articles.id, articles.name, description, content, date, CONCAT(authors.firstname, ' ', authors.lastname) AS author, categories.name AS category FROM articles, authors, categories {$tables} WHERE {$rule} AND author_id = authors.id AND category_id = categories.id";
    }

    // успешное выполнение
    private static function getSuccessResponse($data)
    {
        return [
            'complete' => true,
            'result' => $data
        ];
    }

    // ошибка
    private static function getErrorResponse($message)
    {
        return [
            'complete' => false,
            'message' => $message
        ];
    }
}
