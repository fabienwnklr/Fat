<?php

namespace app\core\exeption;

class ForbidenException extends \Exception
{
    protected $message = 'You don\'t have permission to access this page';
    protected $code = 403;
}