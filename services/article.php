<?php
    function articleList(Language $lang): bool|array
    {
        $sql = 'SELECT a.id, author_id, title_%s as title, 
       created_at, text_%s as text FROM article a JOIN user u on u.id = a.author_id ORDER BY created_at DESC';
        $sql = sprintf($sql, $lang->value, $lang->value);
        $statement = db()->prepare($sql);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    function getArticle(int $id, Language $lang)
    {
        $sql = 'SELECT id, author_id, title_%s as title, created_at, text_%s as text FROM article a WHERE a.id = :id';
        $sql = sprintf($sql, $lang->value, $lang->value);
        $statement = db()->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->execute();

        return $statement->fetch();
    }

    function editArticle(int $articleId, string $title, string $text, Language $lang): int
    {
        $sql = 'UPDATE article SET title_%s = :title, text_%s = :text WHERE id = :articleId';
        $sql = sprintf($sql, $lang->value, $lang->value);

        $statement = db()
            ->prepare($sql);
        $statement->bindValue('title', $title);
        $statement->bindValue('text', $text);
        $statement->bindValue('articleId', $articleId);
        return $statement->execute();
    }

    function createArticle(int $authorId, string $title, string $text, Language $lang): bool|string
    {
        $sql = 'INSERT INTO article (author_id, title_%s, text_%s, created_at) VALUES (:author_id, :name, :text, NOW())';
        $sql = sprintf($sql, $lang->value, $lang->value);
        $statement = db()->prepare($sql);
        $statement->bindValue('author_id', $authorId);
        $statement->bindValue('name', $title);
        $statement->bindValue('text', $text);

        $statement->execute();
        return db()->lastInsertId();
    }