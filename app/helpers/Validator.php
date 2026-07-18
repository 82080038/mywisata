<?php

/**
 * MyWisata Application - Validator Class
 *
 * Handles input validation with various validation rules.
 *
 * @version 1.0.0
 *
 * @since 2026-06-30
 */
class Validator
{
    private $data;
    private $errors = [];

    /**
     * Constructor
     *
     * @param array $data Data to validate
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Validate required fields
     *
     * @param array $fields Fields to check
     *
     * @return self
     */
    public function required($fields)
    {
        foreach ($fields as $field) {
            if (empty($this->data[$field]) && $this->data[$field] !== '0') {
                $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' wajib diisi';
            }
        }

        return $this;
    }

    /**
     * Validate email format
     *
     * @param string $field Field name
     *
     * @return self
     */
    public function email($field)
    {
        foreach ((array) $field as $f) {
            if (!empty($this->data[$f]) && !filter_var($this->data[$f], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$f] = 'Format email tidak valid';
            }
        }

        return $this;
    }

    /**
     * Validate minimum length
     *
     * @param string $field Field name
     * @param int $length Minimum length
     *
     * @return self
     */
    public function min($field, $length)
    {
        foreach ((array) $field as $f) {
            if (!empty($this->data[$f]) && strlen($this->data[$f]) < $length) {
                $this->errors[$f] = 'Minimal ' . $length . ' karakter';
            }
        }

        return $this;
    }

    /**
     * Validate maximum length
     *
     * @param string $field Field name
     * @param int $length Maximum length
     *
     * @return self
     */
    public function max($field, $length)
    {
        foreach ((array) $field as $f) {
            if (!empty($this->data[$f]) && strlen($this->data[$f]) > $length) {
                $this->errors[$f] = 'Maksimal ' . $length . ' karakter';
            }
        }

        return $this;
    }

    /**
     * Validate numeric value
     *
     * @param string $field Field name
     *
     * @return self
     */
    public function numeric($field)
    {
        foreach ((array) $field as $f) {
            if (!empty($this->data[$f]) && !is_numeric($this->data[$f])) {
                $this->errors[$f] = 'Harus berupa angka';
            }
        }

        return $this;
    }

    /**
     * Validate integer value
     *
     * @param string $field Field name
     *
     * @return self
     */
    public function integer($field)
    {
        foreach ((array) $field as $f) {
            if (!empty($this->data[$f]) && !filter_var($this->data[$f], FILTER_VALIDATE_INT)) {
                $this->errors[$f] = 'Harus berupa bilangan bulat';
            }
        }

        return $this;
    }

    /**
     * Validate match with another field
     *
     * @param string $field Field name
     * @param string $matchField Field to match
     *
     * @return self
     */
    public function match($field, $matchField)
    {
        if (!empty($this->data[$field]) && $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $matchField)) . ' tidak cocok';
        }

        return $this;
    }

    /**
     * Validate phone number format
     *
     * @param string $field Field name
     *
     * @return self
     */
    public function phone($field)
    {
        foreach ((array) $field as $f) {
            if (!empty($this->data[$f]) && !preg_match('/^[0-9\+\-\(\)\s]+$/', $this->data[$f])) {
                $this->errors[$f] = 'Format nomor telepon tidak valid';
            }
        }

        return $this;
    }

    /**
     * Validate URL format
     *
     * @param string $field Field name
     *
     * @return self
     */
    public function url($field)
    {
        foreach ((array) $field as $f) {
            if (!empty($this->data[$f]) && !filter_var($this->data[$f], FILTER_VALIDATE_URL)) {
                $this->errors[$f] = 'Format URL tidak valid';
            }
        }

        return $this;
    }

    /**
     * Validate date format
     *
     * @param string $field Field name
     * @param string $format Date format (default: Y-m-d)
     *
     * @return self
     */
    public function date($field, $format = 'Y-m-d')
    {
        foreach ((array) $field as $f) {
            if (!empty($this->data[$f])) {
                $date = DateTime::createFromFormat($format, $this->data[$f]);

                if (!$date || $date->format($format) !== $this->data[$f]) {
                    $this->errors[$f] = 'Format tanggal tidak valid (gunakan: ' . $format . ')';
                }
            }
        }

        return $this;
    }

    /**
     * Validate value is in allowed values
     *
     * @param string $field Field name
     * @param array $allowed Allowed values
     *
     * @return self
     */
    public function in($field, $allowed)
    {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $allowed)) {
            $this->errors[$field] = 'Nilai tidak valid';
        }

        return $this;
    }

    /**
     * Check if validation failed
     *
     * @return bool
     */
    public function fails()
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation passed
     *
     * @return bool
     */
    public function passes()
    {
        return empty($this->errors);
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get first error
     *
     * @return string|null
     */
    public function firstError()
    {
        return reset($this->errors) ?: null;
    }

    /**
     * Get error for specific field
     *
     * @param string $field Field name
     *
     * @return string|null
     */
    public function getError($field)
    {
        return $this->errors[$field] ?? null;
    }
}
