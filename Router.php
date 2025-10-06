<?php

class Router
{
    private string $defaultController = "Home";
    private string $defaultAction = "index";

    public function route(): void
    {
        $controllerName = ucfirst($_GET["controller"] ?? $this->defaultController) . "Controller";
        $actionName = $_GET["action"] ?? $this->defaultAction;

        $controllerFile = "controllers/" . $controllerName . ".php";
        if (!file_exists($controllerFile)) {
            die("Controller file does not exist" . $controllerFile);
        }
        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            die("Class not found: {$controllerName}");
        }
        $controller = new $controllerName();

        if (!method_exists($controller, $actionName)) {
            die("Action not found: {$actionName}");
        }
        $controller->$actionName(); // call the method
    }
}