<?php
namespace App\Lib;

class Auth {

    protected $acl;

    public function checkAcl($module, $route): bool
    {
        $this->acl = require "../app/acl/{$module}.php";
        if ($this->_isAcl($route, 'all')) {
            return true;
        } else if (isset($_SESSION["lector"]) && $this->_isAcl($route, "lector")) {
            return true;
        } else if (isset($_SESSION["admin"]) && $this->_isAcl($route, "admin")) {
            return true;
        }
        return false;
    }

    private function _isAcl(string $route, string $key): bool
    {
        return in_array($route, $this->acl[$key]);
    }

}