<?php

namespace Olsgreen\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class User implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var string[]
     */
    protected $fillable = [
        'created_at',
        'updated_at',
        'displayed_as',
        'id',
        'first_name',
        'last_name',
        'initials',
        'email',
        'locale',
    ];

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->fill($data);
    }

    /**
     * Mass set object attributes.
     *
     * @param array $data
     * @return $this]
     */
    public function fill(array $data): User
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Get the objects ID.
     *
     * @return string|null
     */
    public function getId():? string
    {
        return $this->id;
    }

    /**
     * Get array representing the object.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->attributes[$name];
        }

        return null;
    }
}