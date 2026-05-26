<?php
class Database
{
    private $host;
    private $port;
    private $user;
    private $pass;
    private $dbname;

    private $dbh;
    private $stmt;
    private $error;

    public function __construct()
    {
        $this->host = getenv('MYSQL_HOST') ?: 'localhost';
        $this->port = getenv('MYSQL_PORT') ?: '3306';
        $this->user = getenv('MYSQL_USER') ?: 'root';
        $this->pass = getenv('MYSQL_PASSWORD') ?: '';
        $this->dbname = getenv('MYSQL_DATABASE') ?: 'realestatephp_new';

        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        );

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            $this->dbh->exec("SET time_zone = '+07:00'");
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }

    public function query($sql)
    {
        $this->stmt = $this->dbh->prepare($sql);
    }

    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute()
    {
        return $this->stmt->execute();
    }

    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }
}
