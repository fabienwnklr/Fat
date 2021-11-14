<?php

namespace fabwnklr\fat;

abstract class Model
{

    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';
    public const RULE_UNIQUE = 'unique';

    /**
     * Adding data to the model object
     */
    public function loadData(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    abstract public function rules(): array;

    abstract public function labels(): array;

    /**
     * return label for field
     *
     * @param string $attribute
     * @return string
     */
    public function getLabel(string $attribute): string
    {
        return $this->labels()[$attribute] ?? $attribute;
    }

    public array $errors = [];

    /**
     * Validate the model data
     *
     * @return void
     */
    public function validate()
    {
        foreach ($this->rules() as $attribute => $rules) :
            $value = $this->{$attribute};

            foreach ($rules as $rule) :
                $ruleName = $rule;

                if (!is_string($ruleName)) :
                    $ruleName = $rule[0];
                endif;

                if ($ruleName === self::RULE_REQUIRED && !$value) :
                    $this->addErrorForRule($attribute, self::RULE_REQUIRED);
                endif;

                if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)):
                    $this->addErrorForRule($attribute, self::RULE_EMAIL);
                endif;

                if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']):
                    $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
                endif;

                if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']):
                    $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
                endif;

                if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}):
                    $rule['match']  = $this->getLabel($rule['match']);
                    $this->addErrorForRule($attribute, self::RULE_MATCH, $rule);
                endif;

                if ($ruleName === self::RULE_UNIQUE):
                    $className = $rule['class'];
                    $attribute = $rule['attribute'] ?? $attribute;
                    $tableName = $className::tableName();
                    $statement = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $attribute = :attr");
                    $statement->bindValue(":attr", $value);
                    $statement->execute();
                    $record = $statement->fetchObject();

                    if ($record):
                        $this->addErrorForRule($attribute, self::RULE_UNIQUE, ['field' => $this->getLabel($attribute)]);
                    endif;
                endif;

            endforeach;

        endforeach;

        return empty($this->errors);
    }

    /**
     * Function for add error to model for rule
     *
     * @param string $attribute
     * @param string $rule
     * @return void
     */
    private function addErrorForRule(string $attribute, string $rule, $params = [])
    {
        $message = $this->errorMessages()[$rule] ?? '';

        foreach ($params as $key => $value) :
            $message = str_replace("{{$key}}", $value, $message);
        endforeach;

        $this->errors[$attribute][] = $message;
    }

    /**
     * Function for add error to model
     *
     * @param string $attribute
     * @param string $rule
     * @return void
     */
    public function addError(string $attribute, string $message)
    {
        $this->errors[$attribute][] = $message;
    }

    /**
     * Function returning messages corresponding to rules
     * 
     * @return array
     */
    public function errorMessages(): array
    {
        return [
            self::RULE_REQUIRED => 'This field is required',
            self::RULE_EMAIL => 'This field must be a valid email address',
            self::RULE_MIN => 'Min length of this field must be {min}',
            self::RULE_MAX => 'Max length of this field must be {max}',
            self::RULE_MATCH => 'This field must be the same as {match}',
            self::RULE_UNIQUE => 'Record with this {field} already exist'
        ];
    }

    public function hasError(string $attribute): array|bool
    {
        return $this->errors[$attribute] ?? false;
    }

    public function getFirstError(string $attribute): string
    {
        return $this->errors[$attribute][0] ?? '';
    }
}
