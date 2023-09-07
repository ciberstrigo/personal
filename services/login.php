<?php
function login(string $login, string $password) {
    $sql = 'SELECT id, login, password FROM user WHERE login = :login LIMIT 1';
    $statement = db()->prepare($sql);
    $statement->bindValue(':login', $login);
    $statement->execute();
    $user = $statement->fetch( PDO::FETCH_ASSOC);

    if (empty($user)) {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    return $user;
}