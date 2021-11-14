<?php

namespace fabwnklr\fat;

use fabwnklr\fat\exeption\ForbidenException;
use fabwnklr\fat\exeption\NotFoundException;
use fabwnklr\fat\Request;
use fabwnklr\fat\Response;
use fabwnklr\fat\middlewares\BaseMiddleware;

/**
 * Class Router
 * 
 * @author Fabien <fabien.winkler@outlook.fr>
 * @package fabwnklr\fat
 */
class Router
{
    public Request $request;
    public Response $response;

    protected array $routes = [];

    /**
     * Router constructor.
     * 
     * @param fabwnklr\fat\Request $request
     * @param fabwnklr\fat\Response $response
     */
    function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    function get(String $path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    function post(String $path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;

        if (!$callback) :
            $this->response->setStatusCode(404);
            throw new NotFoundException();
        endif;

        if (is_string($callback)) :
            return Application::$app->view->renderView($callback);
        endif;
        
        if (is_array($callback)) :
            /** @var Controller $controller */
            $controller = new $callback[0]();
            Application::$app->controller = $controller;
            $controller->action = $callback[1];
            $callback[0] = $controller;

            foreach ($controller->getMiddleware() as $middleware) :
                /** @var BaseMiddleware $middleware */
                $middleware->execute();
            endforeach;

        endif;

        return call_user_func($callback, $this->request, $this->response);
        
    }
}