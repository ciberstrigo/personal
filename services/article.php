<?php
    function articleList() {
        $sql = 'SELECT a.id, name, u.login, created_at FROM article a JOIN user u on u.id = a.author_id';
        $statement = db()->prepare($sql);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    function getArticle(int $id) {
        $sql = 'SELECT * FROM article a WHERE a.id = :id';
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