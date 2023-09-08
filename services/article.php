<?php
    function articleList(string $lang = 'en') {
        $sql = 'SELECT a.id, author_id, name%s as name, created_at, text%s as text FROM article a JOIN user u on u.id = a.author_id';
        $lang = $lang === 'en' ? '' : '_'.$lang;
        $sql = sprintf($sql, $lang, $lang);
        $statement = db()->prepare($sql);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    function getArticle(int $id, string $lang = 'en') {
        $sql = 'SELECT id, author_id, name%s as name, created_at, text%s as text FROM article a WHERE a.id = :id';
        $lang = $lang === 'en' ? '' : '_'.$lang;
        $sql = sprintf($sql, $lang, $lang);
        $statement = db()->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->execute();

        return $statement->fetch();
    }

    function editArticle(int $articleId, string $title, string $text): int
    {
        $statement = db()
            ->prepare('UPDATE article SET name = :title, text = :text WHERE id = :articleId');
        $statement->bindValue('title', $title);
        $statement->bindValue('text', $text);
        $statement->bindValue('articleId', $articleId);
        return $statement->execute();
    }

    function createArticle(int $authorId, string $title, string $text)
    {
        $sql = 'INSERT INTO article (author_id, name, text, created_at) VALUES (:author_id, :name, :text, NOW())';
        $statement = db()->prepare($sql);
        $statement->bindValue('author_id', $authorId);
        $statement->bindValue('name', $title);
        $statement->bindValue('text', $text);

        if ($statement->execute()) {
            return db()->lastInsertId();
        } else {
            return null;
        }
    }