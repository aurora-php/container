<?php

/*
 * This file is part of the 'octris/container' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Octris;

/**
 * Implementation of a dependency injection container.
 *
 * @copyright   copyright (c) 2011-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Container implements \Psr\Container\ContainerInterface
{
    /**
     * Storage flags.
     */
    const ACCESS_READONLY = 1;
    const ACCESS_SHARED = 2;

    /**
     * Stores container items.
     *
     * @type    array
     */
    protected array $container = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Set a property. This method enhance the possibility of setting properties by allowing to set shared
     * properties. This is useful to wrap closures to always return same value for the same instance of container.
     *
     * @param   string      $id         Name of property to set.
     * @param   mixed       $value      Value of property to set.
     * @param   int         $flags      Optional flags for property storage.
     * @return  self                    Container instance.
     */
    public function set(string $id, mixed $value, int $flags = 0): self
    {
        if ($this->has($id) && $this->container[$id]['readonly']) {
            throw new \Octris\Container\ReadOnlyException('Unable to overwrite readonly property "' . $id . '"');
        } else {
            $shared   = (($flags & self::ACCESS_SHARED) == self::ACCESS_SHARED);
            $readonly = (($flags & self::ACCESS_READONLY) == self::ACCESS_READONLY);

            if (!$shared || !is_callable($value)) {
                $this->container[$id] = [
                    'value' => $value,
                    'readonly' => $readonly
                ];
            } else {
                $this->container[$id] = [
                    'value'    =>
                        function ($instance) use ($value) {
                            static $return = null;

                            if (is_null($return)) {
                                $return = $value($instance);
                            }

                            return $return;
                        },
                    'readonly' => $readonly
                ];
            }
        }

        return $this;
    }

    public function __set(string $id, mixed $value): void
    {
        $this->set($id, $value);
    }

    /**
     * Get item value from container.
     * 
     * @param   string      $id         Id of item.
     * @return  mixed
     */
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new \Octris\Container\NotFoundException('Unknown identifier "' . $id . '"');
        } else {
            if (is_callable($this->container[$id]['value'])) {
                $cb = $this->container[$id]['value'];
                $return = $cb($this);
            } else {
                $return = $this->container[$id]['value'];
            }
        }

        return $return;
    }

    public function __get(string $id): mixed
    {
        return $this->get($id);
    }

    /**
     * Remove an item from container.
     *
     * @param   string      $id         Id of item to remove.
     */
    public function __unset(string $id): void
    {
        if (isset($this->container[$id])) {
            if ($this->container[$id]['readonly']) {
                throw new \Octris\Container\ReadOnlyException('Unable to unset readonly property "' . $id . '"');
            } else {
                unset($this->container[$id]);
            }
        }
    }

    /**
     * Check if an item is available in container.
     *
     * @param   string      $id         Id of item.
     * @return  bool
     */
    public function has(string $id): bool
    {
        return (isset($this->container[$id]));
    }

    public function __isset(string $id): bool
    {
        return $this->has($id);
    }
}
