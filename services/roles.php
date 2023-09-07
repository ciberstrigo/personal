<?php
    function getRoles(int $id): array|false
    {
        $sql = 'SELECT role FROM user WHERE id=:id LIMIT 1';
        $statement = db()->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->execute();
        $roles = $statement->fetchColumn( 0);

        if (empty($roles)) {
            return false;
            // TODO error user not found
        }

        return json_decode($roles, true);
    }

    function isAdmin(int $id): bool
    {
        return in_array('admin', getRoles($id));
    }