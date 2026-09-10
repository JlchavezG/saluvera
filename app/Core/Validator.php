<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $messages = [];
    private array $customMessages = [];

    public function __construct(array $data = [], array $rules = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->setDefaultMessages();
    }

    public static function make(array $data, array $rules, array $messages = []): self
    {
        $validator = new self($data, $rules);
        $validator->customMessages = $messages;
        return $validator;
    }

    private function setDefaultMessages(): void
    {
        $this->messages = [
            'required' => 'El campo :field es obligatorio',
            'email' => 'El campo :field debe ser un correo electronico valido',
            'numeric' => 'El campo :field debe ser un numero',
            'integer' => 'El campo :field debe ser un numero entero',
            'string' => 'El campo :field debe ser texto',
            'min' => 'El campo :field debe tener al menos :param caracteres',
            'max' => 'El campo :field no debe exceder :param caracteres',
            'min_value' => 'El campo :field debe ser mayor o igual a :param',
            'max_value' => 'El campo :field debe ser menor o igual a :param',
            'date' => 'El campo :field debe ser una fecha valida',
            'url' => 'El campo :field debe ser una URL valida',
            'alpha' => 'El campo :field solo puede contener letras',
            'alpha_num' => 'El campo :field solo puede contener letras y numeros',
            'confirmed' => 'El campo :field no coincide',
            'unique' => 'El campo :field ya existe',
            'exists' => 'El campo :field no existe',
            'in' => 'El campo :field debe ser uno de los siguientes valores: :param',
            'regex' => 'El campo :field tiene un formato invalido',
            'phone' => 'El campo :field debe ser un telefono valido',
            'password' => 'La contrasena debe tener al menos 8 caracteres, una mayuscula y un numero',
        ];
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rulesString) {
            $rules = is_string($rulesString) ? explode('|', $rulesString) : $rulesString;
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $param = null;

                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }

                $methodName = 'validate' . ucfirst(str_replace('_', '', $rule));

                if (method_exists($this, $methodName)) {
                    if (!$this->$methodName($field, $value, $param)) {
                        $this->addError($field, $rule, $param);
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->validate();
    }

    public function passes(): bool
    {
        return $this->validate();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(?string $field = null): ?string
    {
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }

        return null;
    }

    private function addError(string $field, string $rule, ?string $param): void
    {
        $message = $this->getMessage($field, $rule, $param);
        $this->errors[$field][] = $message;
    }

    private function getMessage(string $field, string $rule, ?string $param): string
    {
        $customKey = "{$field}.{$rule}";

        if (isset($this->customMessages[$customKey])) {
            return $this->customMessages[$customKey];
        }

        if (isset($this->customMessages[$rule])) {
            return $this->customMessages[$rule];
        }

        $message = $this->messages[$rule] ?? "El campo :field es invalido";

        $message = str_replace(':field', $this->formatFieldName($field), $message);

        if ($param !== null) {
            $message = str_replace(':param', $param, $message);
        }

        return $message;
    }

    private function formatFieldName(string $field): string
    {
        return str_replace(['_', '.'], ' ', $field);
    }

    // ========================================================================
    // REGLAS DE VALIDACION
    // ========================================================================

    private function validateRequired(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    private function validateEmail(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateNumeric(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_numeric($value);
    }

    private function validateInteger(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function validateString(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null) {
            return true;
        }

        return is_string($value);
    }

    private function validateMin(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $min = (int) $param;

        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }

        if (is_numeric($value)) {
            return (float) $value >= $min;
        }

        return false;
    }

    private function validateMax(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $max = (int) $param;

        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }

        if (is_numeric($value)) {
            return (float) $value <= $max;
        }

        return false;
    }

    private function validateMinValue(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return (float) $value >= (float) $param;
    }

    private function validateMaxValue(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return (float) $value <= (float) $param;
    }

    private function validateDate(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $format = $param ?? 'Y-m-d';
        $date = DateTime::createFromFormat($format, $value);

        return $date && $date->format($format) === $value;
    }

    private function validateUrl(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validateAlpha(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return preg_match('/^[\pL\s]+$/u', $value) === 1;
    }

    private function validateAlphaNum(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return preg_match('/^[\pL\pN\s]+$/u', $value) === 1;
    }

    private function validateConfirmed(string $field, mixed $value, ?string $param): bool
    {
        $confirmationField = $field . '_confirmation';
        $confirmation = $this->data[$confirmationField] ?? null;

        return $value === $confirmation;
    }

    private function validateIn(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $values = explode(',', $param);
        return in_array($value, $values);
    }

    private function validateRegex(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return preg_match($param, $value) === 1;
    }

    private function validatePhone(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $cleaned = preg_replace('/[\s\-\(\)\.]/', '', $value);
        return preg_match('/^\+?[0-9]{10,15}$/', $cleaned) === 1;
    }

    private function validatePassword(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return strlen($value) >= 8
            && preg_match('/[A-Z]/', $value)
            && preg_match('/[0-9]/', $value);
    }

    private function validateUnique(string $field, mixed $value, ?string $param): bool
    {
        return true;
    }

    private function validateExists(string $field, mixed $value, ?string $param): bool
    {
        return true;
    }

    // ========================================================================
    // UTILIDADES
    // ========================================================================

    public function validated(): array
    {
        $validated = [];

        foreach ($this->rules as $field => $rules) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }

        return $validated;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }
}