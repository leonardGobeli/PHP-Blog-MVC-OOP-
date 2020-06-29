<?php
namespace App\Core;

use App\App;
use App\Lib\Auth;
use App\Core\View;

abstract class Controller {

    protected $view;

    public function __construct(array $route)
    {
        $auth = new Auth();
        if (!$auth->checkAcl($route["module"], $route["route"])) {
            View::errorCode(403);
        }
        $this->view     = new View($route);
    }

    public function loadModel(string $managerName)
    {
		$this->$managerName = App::getInstance()->getManager($managerName);
    }

    public function generateUrl(string $name, array $params = [])
    {
        return App::getInstance()->getUrl($name, $params);
    }
    
    public function getParam(array $params, string $param_name)
    {
        return isset($params[$param_name]) ? $params[$param_name] : "";
    }
}