<?php
    function articleList() {
        $sql = 'SELECT a.id, name, u.login, created_at FROM article a JOIN user u on u.id = a.author_id';
        $statement = db()->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }