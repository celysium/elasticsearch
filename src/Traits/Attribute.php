<?php

namespace Celysium\Elasticsearch\Traits;

use Celysium\Elasticsearch\Exceptions\MissingFieldException;

trait Attribute
{
    protected array $fields = [];
    protected array $attributes = [];

    public function __get($key)
    {
        $this->throwUnknownField($key);
        return $this->attributes[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws MissingFieldException
     */
    public function __set(string $key, mixed $value): void
    {
        $this->throwUnknownField($key);
        $this->attributes[$key] = $value;
    }

    /**
     * @param string $key
     * @return void
     * @throws MissingFieldException
     */
    private function throwUnknownField(string $key): void
    {
        if(!in_array($key, $this->fields)) {
            throw new MissingFieldException($this, $key);
        }
    }

    /**
     * @param array $attributes
     * @return void
     * @throws MissingFieldException
     */
    private function throwUnknownFields(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->throwUnknownField($key);
        }
    }

    private function throwMissingAttributes(array $attributes): void
    {
        $keys = array_keys($attributes);
        foreach ($this->fields as $field) {
            if(!in_array($field, $keys) && $field != 'id') {
                throw new MissingFieldException($this, $field);
            }
        }
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->throwUnknownField($key);
            $this->attributes[$key] = $value;
        }
        return $this;
    }

}