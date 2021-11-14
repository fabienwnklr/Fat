<?php

namespace fabwnklr\fat;

use fabwnklr\fat\middlewares\BaseMiddleware;

class Controller
{
    public string $layout = 'main';
    public string $action = '';
    /**
     * @var BaseMiddleware[]
     */
    public array $middlewares = [];

    public function setLayout(string $layout): void
    {
        $this->layout = $layout; 
    }

    public function render($view, $params = []): string | false
    {
        return Application::$app->view->renderView($view, $params);
    }

    public function registerMiddleware(BaseMiddleware $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function getMiddleware(): BaseMiddleware|Array
    {
        return $this->middlewares;
    }
}