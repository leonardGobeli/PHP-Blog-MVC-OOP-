<?php
namespace App\Lib;

use PDO;

class Database {

    protected $pdo;

    protected $config;
	
    public function __construct() 
    {
		$this->config = require "../app/config/db.php";
    }

    /**
     * Initializes the connection to the database.
     * If $pdo is not defined creates a PDO instance
     *
     * @return PDO 
     */
    protected function getPDO(): PDO
    {
        if ($this->pdo === null) {
            $pdo = new PDO("mysql:host={$this->config['host']};dbname={$this->config['name']}", $this->config["user"], $this->config["password"]);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo = $pdo;
        }
        return $this->pdo;
    }
    
    /**
     * Recover data
     *
     * @param string $statement
     * @param array|null $vars
     * @param boolean $all
     * @param string|null $entity
     * @param boolean $recover
     * @return mixed
     */
    public function query(string $statement, ?array $vars = null, bool $all = false, ?string $entity = null, bool $recover = false): mixed
    {
        if ($recover && $vars) {
            $res = $this->prepare($statement, $vars);
        } else {
            $res = $this->getPDO()->query($statement);
        }

        if ($entity) {
            $res->setFetchMode(PDO::FETCH_CLASS, $entity);
        } else {
            $res->setFetchMode(PDO::FETCH_OBJ);
        }

        if ($all) {
            $data = $res->fetchAll();
        } else {
            $data = $res->fetch();
        }

        $res->closeCursor();

        return $data;
    }

    /**
     * Insert data
     *
     * @param string $statement
     * @param array $vars
     * // param boolean $recover // retirer en attendant de vérifier si tout fonctione bien comme ceci
     * @return mixed|void
     */
    public function prepare(string $statement, array $vars)
    {
        $req = $this->getPDO()->prepare($statement);
        
        foreach ($vars as $param => $val) {
            if (is_int($val)) {
                $req->bindValue($param, $val, PDO::PARAM_INT);
            } elseif (is_string($val)) {
                $req->bindValue($param, $val, PDO::PARAM_STR);
            } else {
                $req->bindValue($param, $val);
            }
        }

        $req->execute();

        return $req;

        $req->closeCursor();
    }

    /** 
     * Execute an SQL query and return the number of affected rows
     * 
     * @param string $statement
     * @return int
    */
    public function exec(string $statement): int
    {
        return $this->getPDO()->exec($statement);
    }

    /**
     * Returns a single column from the next row of a result set
     *
     * @param string $statement
     * @return mixed 
     */
    public function column(string $statement): mixed
    {
        return $this->getPDO()->query($statement)->fetchColumn();
    }

    /**
     * Get the id of the last insertion
     *
     * @return string 
     */
    public function lastInsertId(): string
    {
        return $this->getPDO()->lastInsertId();
    }

}
