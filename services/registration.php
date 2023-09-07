<?php
function registration(string $login, string $password): bool
{
    $sql = 'INSERT INTO user(login, password, role)
            VALUES(:login, :password, :role)';


    $statement = db()->prepare($sql);
    $statement->bindValue(':login', $login);
    $statement->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
    $statement->bindValue(':role', json_encode(['user']));

    return $statement->execute();
}

function alreadyRegistered(string $username): bool
{
    $sql = 'SELECT COUNT(*) FROM user WHERE login = :login LIMIT 1';
    $statement = db()->prepare($sql);
    $statement->bindValue(':login', $username);

    $statement->execute();
    return $statement->fetch( PDO::FETCH_COLUMN, 0);
}