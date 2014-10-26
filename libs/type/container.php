<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace octris\core\type;

/**
 * Implementation of a dependency injection container as generic instanciable datatype.
 *
 * @octdoc      c:type/container
 * @copyright   copyright (c) 2011 by Harald Lapp
 * @author      Harald Lapp <harald.lapp@gmail.com>
 */
class container 
{
    /**
     * Storage flags.
     * 
     * @octdoc  d:container/T_READONLY, T_SHARED
     */
    const T_READONLY = 1;
    const T_SHARED   = 2;
    /**/
    
    /**
     * Stores container items.
     *
     * @octdoc  p:container/$container
     * @type    array
     */
    protected $container = array();
    /**/
    
    /**
     * Set a property.
     *
     * @octdoc  m:container/__set
     * @param   string      $name       Name of property to set.
     * @param   mixed       $value      Value of property to set.
     */
    public function __set($name, $value)
    {
        if (isset($this->container[$name]) && $this->container[$name]['readonly']) {
            throw new \Exception("unable to overwrite readonly property '$name'");
        } else {
            $this->container[$name] = array(
                'value'    => $value,
                'readonly' => false
            );
        }
    }
    
    /**
     * Set a property. This method enhance the possibility of setting properties by allowing to set shared
     * properties. This is useful to wrap closures to always return same value for the same instance of container.
     *
     * @octdoc  m:container/set
     * @param   string      $name       Name of property to set.
     * @param   mixed       $value      Value of property to set.
     * @param   int         $flags      Optional flags for property storage.
     */
    public function set($name, $value, $flags = 0)
    {
        if (isset($this->container[$name]) && $this->container[$name]['readonly']) {
            throw new \Exception("unable to overwrite readonly property '$name'");
        } else {
            $shared   = (($flags & self::T_SHARED) == self::T_SHARED);
            $readonly = (($flags & self::T_READONLY) == self::T_READONLY);
        
            if (!$shared || !is_callable($value)) {
                $this->container[$name] = array(
                    'value'    => $value,
                    'readonly' => $readonly
                );
            } else {
                $this->container[$name] = array(
                    'value'    => 
                        function ($instance) use ($value) {
                            static $return = null;

                            if (is_null($return)) {
                                $return = $value($instance);
                            }

                            return $return;
                        },
                    'readonly' => $readonly
                );
            }
        }
    }
    
    /**
     * Magic getter returns value of stored container, callbacks will be called.
     *
     * @octdoc  m:container/__get
     * @param   string      $name       Name of container to return.
     */
    public function __get($name)
    {
        $return = null;
        
        if (!isset($this->container[$name])) {
            throw new \Exception("container '$name' is not set!");
        } else {
            if (is_callable($this->container[$name]['value'])) {
                $cb = $this->container[$name]['value'];
                $return = $cb($this);
            } else {
                $return = $this->container[$name]['value'];
            }
        }
        
        return $return;
    }
    
    /**
     * Unset a container.
     *
     * @octdoc  m:container/__unset
     * @param   string      $name       Name of container to unset.
     */
    public function __unset($name)
    {
        if (isset($this->container[$name])) {
            if ($this->container[$name]['readonly']) {
                throw new \Exception("unable to unset readonly property '$name'");
            } else {
                unset($this->container[$name]);
            }
        }
    }
    
    /**
     * Check if a container is set
     *
     * @octdoc  m:container/__isset
     * @param   string      $name       Name of container to test.
     */
    public function __isset($name)
    {
        return (isset($this->container[$name]));
    }
}

