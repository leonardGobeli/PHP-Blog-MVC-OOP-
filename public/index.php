<?php

require "../app/lib/dev.php";

use App\Lib\Test;
use App\Core\Router;

// 1- Manage class autoload
spl_autoload_register(function($class) {
    $path = '../' . str_replace(
        ["App", "Core", "Lib", "Controllers", "Models", "Views", "\\"], 
        ["app", "core", "lib", "controllers", "models", "views", "/"], 
        $class . '.php');
    if (file_exists($path)) {
        require $path;
    }
});

// 2- Activate sessions
session_start();

// $test = new Test();
// $test->setUpdated_at(new DateTime("now", new DateTimeZone('Europe/Paris')));
// $test->setPublished_at(new DateTime("2020-06-29", new DateTimeZone('Europe/Paris')));
// $test->sortEntries("where", ["deleted_at" => "IS NOT NULL"], ["3" => ":currentItem", 4 => ":itemsPerPage"]); // Edition
// $test->sortEntries("order by", ["published_at" => "< CURRENT_DATE()"], ["0" => ":currentItem", 4 => ":itemsPerPage"]);
// $test->sortEntries("where", ["published_at" => "> CURRENT_DATE()"]);
// $test->generateSqlParts(null, false, ["id" => 1]);

// 3- Instantiate the Router and launch it
$router = new Router();
$router->run();