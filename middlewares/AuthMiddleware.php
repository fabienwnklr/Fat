<?php

namespace fabwnklr\fat\middlewares;

use fabwnklr\fat\Application;
use fabwnklr\fat\exeption\ForbidenException;

class AuthMiddleware extends BaseMiddleware
{
    public array $actions;

    /**
     * @param array $actions
     */
    public function __construct(array $actions = [])
    {
        $this->actions = $actions;
    }

    public function execute()
    {
        if (Application::isGuest()) :
            if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)):
                throw new ForbidenException();
            endif;
        endif;
    }
}