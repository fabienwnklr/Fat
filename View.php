<?php

namespace app\core;

class View
{
    public string $title = '';

    public function renderView(String $view, array $params = [])
    {
        $layoutName = Application::$app->layout;
        
        if (Application::$app->controller) :
            $layoutName = Application::$app->controller->layout;
        endif;

        $viewContent = $this->renderOnlyView($view, $params);

        ob_start();
        include_once Application::$ROOT_DIR . "/views/layouts/$layoutName.php";
        $layoutContent = ob_get_clean();
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    public function renderContent(String $viewContent)
    {
        $layoutContent = $this->layoutContent();

        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    protected function layoutContent()
    {
        $layout = Application::$app->layout;

        if (Application::$app->controller) :
            $layout = Application::$app->controller->layout;
        endif;

        ob_start();
        include_once Application::$ROOT_DIR . "/views/layouts/$layout.php";
        return ob_get_clean();
    }

    protected function renderOnlyView(string $view, array $params)
    {
        foreach ($params as $key => $value) :
            $$key = $value;
        endforeach;

        ob_start();
        include_once Application::$ROOT_DIR . "/views/$view.php";
        return ob_get_clean();
    }
}