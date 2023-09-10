<?php
const LIMIT_TO_POST_SECONDS = 30;
const MAX_LENGTH = 300;

function createComment(
    string $captcha,
    int $articleId,
    int $authorId,
    string $text,
    ?string $replyTo = null
): bool|string
{
    if ($captcha !== $_SESSION['captcha']) {
        flash('comments', 'Captcha is incorrect!', FLASH_ERROR);
        return false;
    }

    if (strlen($text) > MAX_LENGTH) {
        flash('comments', 'Text is too long!', FLASH_ERROR);
        return false;
    }

    $sql = '
        SELECT TIMESTAMPDIFF(SECOND, c.created_at, NOW()) 
        FROM comment c 
        WHERE c.author_id = :author_id 
        ORDER BY c.created_at DESC limit 1
    ';
    $statement = db()->prepare($sql);
    $statement->bindValue('author_id', $authorId);
    $statement->execute();
    $seconds = $statement->fetch(PDO::FETCH_COLUMN, 0);

    if ($seconds && $seconds < LIMIT_TO_POST_SECONDS) {
        flash(
            'comments',
            'You are posting too fast. Try after '.(LIMIT_TO_POST_SECONDS - $seconds).' seconds.',
            FLASH_ERROR
        );
        return false;
    }

    $sql = '
        INSERT INTO comment (article_id, text, reply_to, author_id, created_at) 
        VALUES (:article_id, :text, :reply_to, :author_id, NOW())
    ';
    $statement = db()->prepare($sql);
    $statement->bindValue('article_id', $articleId);
    $statement->bindValue('text', $text);
    $statement->bindValue('reply_to', $replyTo);
    $statement->bindValue('author_id', $authorId);

    try {
        $statement->execute();
    } catch (PDOException $exception) {
        flash('comments', 'Incorrect text. Please do not use emoji or any specific symbols.', FLASH_ERROR);
        return false;
    }

    return db()->lastInsertId();
}

function getComments(int $articleId): array
{
    $sql = '
        SELECT c.id, c.article_id as article_id, c.text as text, c.reply_to as reply_to, c.author_id as author_id, u.login as author, c.created_at, 
        replied.text as replied_text, replied.author_id as replied_author_id, replied.id as replied_id, ru.login as replied_login
        FROM comment c JOIN user u on c.author_id = u.id 
        LEFT JOIN comment replied ON c.reply_to = replied.id
        Left JOIN user ru ON ru.id = replied.author_id
        WHERE c.article_id = :articleId AND c.is_deleted = FALSE ORDER BY c.created_at DESC
    ';
    $statement = db()->prepare($sql);
    $statement->bindValue(':articleId', $articleId);
    $statement->execute();

    return $statement->fetchAll();
}

function deleteComment($commentId): bool
{
    $sql = 'UPDATE comment SET text="__deleted__", is_deleted=TRUE WHERE id = :commentId';

    $statement = db()->prepare($sql);
    $statement->bindValue(':commentId', $commentId);
    $statement->execute();

    return $statement->execute();
}
