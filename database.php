<?php

class database
{
    public $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=j1026875.myjino.ru;dbname=j1026875_bot', 'j1026875', '201420341s', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    public function get_update_id()
    {
        try {
            $obj = $this->pdo->prepare("select update_id from update_table where vers =1");
            $obj->execute();
            $ans = $obj->fetchAll();
            return $ans;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function set_insert_id($update)
    {
        try {
            $obj = $this->pdo->prepare("insert into update_table values(1,:upd)");
            $obj->execute(['upd' => $update]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function set_update_id($update)
    {
        try {
            $obj = $this->pdo->prepare("update update_table set update_id =:upd where vers=1");
            $obj->execute(['upd' => $update]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function get_user_id($login, $password)
    {
        try {
            $obj = $this->pdo->prepare("select user_id from users where login=:log and password=:pass");
            $obj->execute(['log' => $login, 'pass' => $password]);
            $ans = $obj->fetchAll();
            return $ans;
        } catch (PDOException $exception) {
            return false;
        }

    }

    public function get_user_id_using_login($login)
    {
        try {
            $obj = $this->pdo->prepare("select user_id from users where login=:log ");
            $obj->execute(['log' => $login]);
            $ans = $obj->fetchAll();
            return $ans;
        } catch (PDOException $exception) {
            return false;
        }

    }

    public function add_user($login, $password)
    {
        try {
            $obj = $this->pdo->prepare("insert into users(login,password) values(:log,:pass)");
            $obj->execute(['log' => $login, 'pass' => $password]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function delete_user($login, $password)
    {
        try {
            $obj = $this->pdo->prepare("delete from users where login=:log and password=:pass");
            $obj->execute(['log' => $login, 'pass' => $password]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function set_new_session($user_id, $chat_id, $status)
    {
        try {
            $obj = $this->pdo->prepare("insert into sessions values (:chat,:user,:stat)");
            $obj->execute(['chat' => $chat_id, 'user' => $user_id, 'stat' => $status]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function update_session($user_id, $chat_id, $status)
    {
        try {
            $obj = $this->pdo->prepare("update sessions set status=:stat where user_id=:usr and chat_id=:chat");
            $obj->execute(['stat' => $status, 'usr' => $user_id, 'chat' => $chat_id]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function delete_session($user_id, $chat_id)
    {
        try {
            $obj = $this->pdo->prepare("delete from sessions where user_id=:usr and chat_id=:chat");
            $obj->execute(['usr' => $user_id, 'chat' => $chat_id]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function get_user_id_of_current_session($chat_id)
    {
        try {
            $obj = $this->pdo->prepare("select user_id from sessions where chat_id=:chat and status=1");
            $obj->execute(['chat' => $chat_id]);
            $ans = $obj->fetchAll();
            return $ans;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function set_file($user_id, $path)
    {
        try {
            $obj = $this->pdo->prepare("insert into files(user_id,path) values (:user,:filepath)");
            $obj->execute(['user' => $user_id, 'filepath' => $path]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function get_file($user_id, $name)
    {
        try {
            $obj = $this->pdo->prepare("select path from files where user_id=:user and path like :nm");
            $string = '%' . $name . '%';
            $obj->execute(['user' => $user_id, 'nm' => $string]);
            $ans = $obj->fetchAll();
            return $ans;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function delete_file($user_id, $path)
    {
        try {
            $obj = $this->pdo->prepare('delete from files where user_id=:usr and path=:pth');
            $obj->execute(['usr' => $user_id, 'pth' => $path]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function delete_all_files_of_user($user_id)
    {
        try {
            $obj = $this->pdo->prepare('delete from files where user_id=:usr');
            $obj->execute(['usr' => $user_id]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function get_all_files($user_id)
    {
        try {
            $obj = $this->pdo->prepare('select path from files where user_id=:usr');
            $obj->execute(['usr' => $user_id]);
            $ans = $obj->fetchAll();
            return $ans;
        } catch (PDOException $exception) {
            return false;
        }
    }

}

