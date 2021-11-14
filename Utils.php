<?php

namespace fabwnklr\fat;

class Utils
{
    public static function dump($data): void
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        exit;
    }
}