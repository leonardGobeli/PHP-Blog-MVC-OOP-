<?php
namespace App\Core;

class View {

    protected $viewPath;

    protected $layout = "default";

    public function __construct(array $route)
    {
        // Si l'action est un GET il à une vue 
        if ($route["method"] === "GET") {
            $this->viewPath = "../app/views/{$route['module']}/{$route['controller']}/{$route['action']}.php";
        }
    } 

    public function render(string $title = "Jean Forteroche - Billet simple pour l'Alaska", array $vars = [])
    {
        extract($vars);
        if (file_exists($this->viewPath)) {
			ob_start();
			require $this->viewPath;
			$content = ob_get_clean();
			require "../app/views/layouts/{$this->layout}.php";
		} else {
            echo "ok il ne manque plus que la page \"{$title}\"";
        }
    }

    public function redirect($url) {
		header("location: {$url}");
		exit;
	}

    public static function errorCode($code)
    {
        http_response_code($code);
        $path = "../app/views/errors/{$code}.php";
        if (file_exists($path)) {
			require $path;
        }
        echo "{$code}";
		exit;
    }
}