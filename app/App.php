<?php
namespace App;

use App\Core\Router;
use App\Lib\Database;


class App {
    
    private static $_instance;

    private $_dbInstance;

    private $_routerInstance;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new App();
        }
        return self::$_instance; 
    }

    public function getUrl(string $name, array $params = [])
    {
        if ($this->_routerInstance === NULL) {
            $this->_routerInstance = new Router();
        }
        return $this->_routerInstance->generate($name, $params);
    }

    public function getManager(string $managerName)
    {
        $className = "App\Managers\\".ucfirst($managerName)."Manager";
		if (class_exists($className)) {
			return new $className($this->getDb());
		}
    }

    protected function getDb(): DataBase
    {
        if ($this->_dbInstance === NULL) {
            $this->_dbInstance = new Database();
        }
        return $this->_dbInstance;
    }

}