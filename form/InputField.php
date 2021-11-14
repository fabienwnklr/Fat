<?php

namespace app\core\form;

use app\core\Model;

class InputField extends BaseField
{
    public const TYPE_TEXT = 'text';
    public const TYPE_EMAIL = 'email';
    public const TYPE_PASSWORD = 'password';

    public string $type;
    public Model $model;
    public string $attribute;

    /**
     * Field constructor.
     *
     * @param Model $model
     * @param string $attribute
     */
    public function __construct(Model $model, string $attribute)
    {
        $this->type = self::TYPE_TEXT;
        parent::__construct($model, $attribute);
    }

    /**
     * Generate input password field
     * 
     * @return string
     */
    public function passwordField()
    {
        $this->type = self::TYPE_PASSWORD;

        return $this;
    }

    public function renderInput(): string
    {
        return sprintf(
            '<input type="%s" name="%s" value="%s" placeholder="%s" class="form-control%s">',
            $this->type,
            $this->attribute,
            $this->model->{$this->attribute},
            $this->model->getLabel($this->attribute),
            $this->model->hasError($this->attribute) ? ' is-invalid' : ''
        );
    }
}
