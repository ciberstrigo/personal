<?php
function login(string $login, string $password): ?array
{
    if (empty($login) || empty($password)) {
        return null;
    }

    $sql = 'SELECT id, login, password FROM user WHERE login = :login LIMIT 1';
    $statement = db()->prepare($sql);
    $statement->bindValue(':login', $login);
    $statement->execute();
    $user = $statement->fetch( PDO::FETCH_ASSOC);

    if (empty($user)) {
        return null;
    }

    if (!password_verify($password, $user['password'])) {
        return null;
    }

    session_regenerate_id();

    $_SESSION['login'] = $user['login'];
    $_SESSION['user_id']  = $user['id'];

    return $user;
}