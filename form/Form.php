<?php

namespace fabwnklr\fat\form;

use fabwnklr\fat\Model;

class Form
{
    public static function begin(string $action = '', string $method = 'post')
    {
        echo sprintf('<form action="%s" method="%s">', $action, $method);

        return new Form();
    }

    public static function end()
    {
        echo '</form>';
    }

    public function field(Model $model, string $attribute)
    {
        return new InputField($model, $attribute);
    }
}