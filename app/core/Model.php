<?php
namespace App\Core;

use App\Lib\Database;

abstract class Model {

    /** @var DataBase */
    protected $db;

    /** @var string */
    protected $table;
	
    public function __construct(Database $db) 
    {
        $this->db = $db;
    }

    /**
     * Generate parts of SQL code that contains variables
     *
     * @param object|null $entity
     * @param array|null $vars
     * @return array|string
     */
    public function generateSqlParts(?object $entity = null, ?array $vars = null, bool $isSort = false)
    {
        if ($vars) {
            $propertys  = $vars;
            $isNew      = false;
        } else {
            $propertys  = $entity->iterate();
            $isNew      = $entity->isNew();
        }

        $parts      = [];
        $values     = [];
        $attr       = [];

        foreach ($propertys as $key => $value) {
            if ($isSort) {
                if (stripos($key, "_at") > 0) {
                    $parts[] = "{$key} {$value}";
                } else {
                    $parts[] = "{$key} = {$value}";
                }
            } else {
                $values[] = ":{$key}";

                if (is_int($key)) {  
                    $attr["{$value}"] = $key; 
                } else {
                    $attr[":{$key}"] = $value;
                }

                if (stripos($key, "_at") > 0) {
                    $attr[":{$key}"] = $value->format("Y-m-d H:i:s");
                } 
                
                if ($isNew) {
                    $parts[] = $key;
                } else if (is_int($key)) {
                    $parts[] = $value;
                } else {
                    $parts[] = "{$key}=:{$key}";
                }
            }
        }

        if ($isNew) {
            return ["parts" => implode(", ", $parts), "values" => implode(", ", $values),  "attr" => $attr];
        } else if ($isSort) {
            return $sqlParts = implode(" ", $parts);
        } else {
            return ["parts" => implode(", ", $parts), "attr" => $attr];
        }
    }

    /**
     * Inserting new data into the database
     *
     * @param object $entity
     * @return mixed
     */
    protected function create(object $entity)
    {
        $sql    = $this->generateSqlParts($entity);
        $parts  = $sql["parts"];
        $values = $sql["values"];
        $attr   = $sql["attr"];

        return $this->query("INSERT INTO {$this->table} ({$parts}) VALUES({$values})", $attr);
    }

    /**
     * Updating data in the database
     *
     * @param object $entity
     * @return mixed
     */
    protected function update(object $entity)
    {
        $sql    = $this->generateSqlParts($entity);
        $parts  = $sql["parts"];
        $attr   = $sql["attr"];

        return $this->query("UPDATE {$this->table} SET {$parts} WHERE id=:id", $attr);
    }
    
    /**
     * Checks whether the instance is valid, new or old. And redirects its flow to the most appropriate method.
     *
     * @param object $entity
     * @return void
     */
    public function save(object $entity)
    {
        if ($entity->isValid()) {
            $entity->isNew() ? $this->create($entity) : $this->update($entity);
        } else {
            throw new RuntimeException("! UNE ERREUR C'EST PRODUITE LORS DE L'INSERTION DE DONNÉES !");
      }
    }

    /**
     * Count the total number of entries
     *
     * @return int
     */
    public function countEntries(): int
    {
        return $this->db->column("SELECT COUNT(*) FROM {$this->table}");
    }

    /**
     * Retrieve a specific entry according to a search value
     *
     * @param array $vars
     * @return mixed
     */
    public function find(array $vars): mixed
    {
        $sql    = $this->generateSqlParts(null, $vars);
        $parts  = $sql["parts"];
        $attr   = $sql["attr"];

        return $this->query("SELECT * FROM {$this->table} WHERE {$parts}", $attr, null, false, true);
    }

    /**
     * Get all entries from a table
     *
     * @return mixed
     */
    public function getAllEntries(): mixed
    {
        return $this->query("SELECT * FROM {$this->table}", null, true);
    }

    /**
     * Sort the data in a table to RETRIEVE OR COUNT the columns concerned
     *
     * @param string $sqlFunction  Name of SQL function used for sorting (ex: "where", "order by", ...)
     * @param array       $params  The attributes concerned by the sort (ex: ["published_at" => "< CURRENT_DATE()"], ["deleted_at" => "IS NOT NULL"], ...)
     * @param array|null   $limit  Used for paging (use: [0 => ":currentItem", 4 => ":itemsPerPage"])     
     * @param boolean        $all  If true retrieves all the posts concerned, if false recover first
     * @param boolean      $count  If true returns the number of lines concerned, if false retrieves the data
     * 
     * @return mixed
     */
    public function sortEntries(string $sqlFunction, array $params, ?array $limit = null, bool $all = false, bool $count = false)
    {
        $attr       = null;
        $parts      = $this->generateSqlParts(null, $params, true);
        $function   = strtoupper($sqlFunction);

        if ($count) {
            return $this->db->column("SELECT COUNT(*) FROM {$this->table} {$function} {$parts}");
        } else {
            $statement  = "SELECT * FROM {$this->table} {$function} {$parts}";
            
            if ($limit) {
                $limit      = $this->generateSqlParts(null, $limit);
                $statement .= " LIMIT {$limit['parts']}";
                $attr       = $limit["attr"];
            }
        }

        if ($attr) {
            return $this->query($statement, $attr, $all, true);
        } else {
            return $this->query($statement, $attr, $all);
        }
    }

    /**
     * Redirect a query to the best way to be processed
     *
     * @param string $statement
     * @param array|null $vars
     * @param boolean $all
     * @param boolean $recover
     * @return void
     */
    public function query(string $statement, ?array $vars = null, bool $all = false, bool $recover = false) 
    {
        if ($vars && $recover) {
            return $this->db->query(
                $statement,
                $vars,
                $all,
                str_replace('Manager', 'Entity', get_class($this)),
                $recover
            );
        } else if ($vars) {
            return $this->db->prepare($statement, $vars);
        } else {
            return $this->db->query(
                $statement,
                $vars,
                $all,
                str_replace('Manager', 'Entity', get_class($this))
            );
        }
    }

}