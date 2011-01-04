<?php

/*
 * Copyright (c) 2011, Harald Lapp <harald.lapp@gmail.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Harald Lapp nor the names of its
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace org\octris\core\type\container {
    /**
     * Implementation of a dependency injection container as generic instanciable  datatype.
     *
     * @octdoc      c:type/container
     * @copyright   copyright (c) 2011 by Harald Lapp
     * @author      Harald Lapp <harald.lapp@gmail.com>
     */
    class container 
    /**/
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
         * @octdoc  v:container/$container
         * @var     array
         */
        protected $container = array();
        /**/
        
        /**
         * Constructor.
         *
         * @octdoc  m:container/__construct
         */
        public function __construct()
        /**/
        {
        }

        /**
         * Set a property.
         *
         * @octdoc  m:container/__set
         * @param   string      $name       Name of property to set.
         * @param   mixed       $value      Value of property to set.
         */
        public function __set($name, $value)
        /**/
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
        /**/
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
                            function($instance) use ($value) {
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
        /**/
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
        /**/
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
        /**/
        {
            return (isset($this->container[$name]));
        }
    }
}
