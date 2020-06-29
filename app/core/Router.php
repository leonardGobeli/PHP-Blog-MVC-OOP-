<?php
namespace App\Core;

use App\Core\View;

class Router {
    
    /**
     * Contains the routes of the app
     *
     * @var array
     */
    protected $routes = [];

    protected $namedRoutes = [];

    protected $matchedRoute = [];

    protected $matchTypes = [
        "i"  => "(\d+)",
        "a"  => "(\w+)",
        "*"  => "(.+)"
    ];

    public function __construct()
    {
        $config = require "../app/config/routes.php";
        
        foreach ($config as $key => $handler) {
            list($route, $name, $controller, $callable, $method) = $handler;
            $this->map($route, $name, $controller, $callable, $method);
        }
    }

    public function map(string $route, string $name, string $controller, string $callable, string $method) 
    {
        $this->routes[] = [$route, $controller, $callable, $method];

        if (!empty($name)) {
            if(!isset($this->namedRoutes[$name])) {
                $this->namedRoutes[$name] = $route;
            }
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
    
    /**
     * Permet de génere un url selon le nom d'une route et ces potentiel params
     *
     * @param string $name Le nom de la route à générer
     * @param array $params Contient les paramètres à transmettre si il y en as 
     * @return string Retourne une url
     */
    public function generate(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new RuntimeException("Route {$name} does not exist.");
        }

        $route  = $this->namedRoutes[$name];
        $url    = $route;

        if (preg_match_all("#{(\w+):(.)}#", $route, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                list($block, $param, $type) = $match;
                
                if (isset($params[$param])) { 
                    $param = str_replace(" ", "_", strtolower($params[$param]));
                    $url = str_replace($block, $param, $url);
                } else {
                    $url = str_replace($block, "", $url);
                }
            }
        }
        return $url;
    }

    public function run()
    {
        if ($this->_match()) {
            $path = "App\Controllers\\" . ucfirst($this->matchedRoute["module"]) . "\\" . ucfirst($this->matchedRoute["controller"]) . "Controller";
            if (class_exists($path)) {
                $callable = $this->matchedRoute["action"];
                if (method_exists($path, $this->matchedRoute["action"])) {
                    $controller = new $path($this->matchedRoute);
                    if (isset($this->matchedRoute["params"])) {
                        $controller->$callable($this->matchedRoute["params"]);
                    } else {
                        $controller->$callable();
                    }
                } else {
                    View::errorCode(404);
                }
            } else {
                View::errorCode(404);
            }
        } else {
            View::errorCode(404);
        }
    }

    private function _match()
    {
        $params         = [];
        $requestUrl     = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "/";
        $requestMethod  = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "GET";
        
        foreach ($this->routes as $handler) {
            list($route, $controller, $callable, $method) = $handler;

            $method_match = (stripos($method, $requestMethod) !== false);

            if (!$method_match) {
                continue;
            }

            if ((strpos($route, "{")) === false) {
                // No params in url, do string comparison
                $match = strcmp($requestUrl, $route) === 0;
            } else {
                $comp   = $this->_compileRoute($route);
                $regex  = $comp["regex"];
                $match  = preg_match($regex, $requestUrl, $params) === 1;
            }  
            
            if ($match) {
                if ($params) {
                    $parameters = [];
                    foreach ($params as $key => $value) {
                        if ($key === 0) {
                            continue;
                        }
                        $parameters[$comp["param"]] = $value;
                    }
                    $this->matchedRoute["params"]   = $parameters;
                }
                $this->matchedRoute["module"]       = preg_match("#^/admin#", $route) ? "admin" : "main";
                $this->matchedRoute["controller"]   = $controller;
                $this->matchedRoute["action"]       = $callable;
                $this->matchedRoute["route"]        = $route;
                $this->matchedRoute["method"]       = $method;
                
                return true;
            }    
        }
        return false;
    }

    /**
     * Compile the regex for a given route
     *
     * @param string $route
     * @return array
     */
    private function _compileRoute(string $route): array
    {

        if (preg_match_all("#{(\w+):(.)}#", $route, $matches, PREG_SET_ORDER)) {
            foreach($matches as $match) {
                list($block, $param, $type) = $match;
                
                if (isset($this->matchTypes[$type])) {
                    $type = $this->matchTypes[$type];
                }
                $route = str_replace($block, $type, $route);
            }
        } 

        return [
            "regex" => "#^{$route}$#",
            "param" => $param
        ];
    }
}
