<?php

namespace fabwnklr\fat\form;

use fabwnklr\fat\Model;

abstract class BaseField
{
    public Model $model;
    public string $attribute;

    /**
     * BaseField constructor.
     *
     * @param Model $model
     * @param string $attribute
     */
    public function __construct(Model $model, string $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }

    abstract public function renderInput(): string;

    /**
     * Generate input text field
     * 
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '
            <div class="form-floating mb-3">
                %s
                <label>%s</label>
                <div class="invalid-feedback">
                    %s
                </div>
            </div>
            ',
            $this->renderInput(),
            $this->model->getLabel($this->attribute),
            $this->model->getFirstError($this->attribute),
        );
    }
}
