<?php

namespace app\core\form;

class TexareaField extends BaseField
{
    public function renderInput(): string
    {
        return sprintf(
            '<textarea name="%s" placeholder="%s" class="form-control%s">%s</textarea>',
            $this->attribute,
            $this->model->getLabel($this->attribute),
            $this->model->hasError($this->attribute) ? ' is-invalid' : '',
            $this->model->{$this->attribute}
        );
    }
}