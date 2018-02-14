<?php

/*
 * This file is part of the OpenStates Framework (osf) package.
 * (c) Guillaume Ponçon <guillaume.poncon@openstates.com>
 * For the full copyright and license information, please read the LICENSE file distributed with the project.
 */

namespace Osf\Container;

use Osf\Exception\ArchException;
use Osf\Container\OsfContainer as Container;

/**
 * Object builder and manager for container classes
 * 
 * @author Guillaume Ponçon <guillaume.poncon@openstates.com>
 * @copyright OpenStates
 * @version 1.0
 * @since OSF-2.0 - 23 sept. 2013
 * @package osf
 * @subpackage container
 */
abstract class AbstractContainer
{
    
    /**
     * Set the mock context in order to generate mock objects
     * Namespace "real" = no mock (production)
     * Namespace "mock" = mock (test)
     * @param string $namespace
     */
    public static function setMockNamespace(string $namespace): void
    {
        static::$mockNamespace = $namespace;
    }
    
    /**
     * Build an object or get existing object from class name and serveral parameters
     * @param string $className class name with namespace
     * @param array $args construct arguments values
     * @param string $instanceName Name of the instance (x names = x instances)
     * @param string $beforeBuildBootstrapMethod method to call in bootstrap file before build the object
     * @param string $afterBuildBootstrapMethod method to call in bootstrap file after build the object
     * @return mixed
     * @throws ArchException
     */
    public static function buildObject(
            $className, 
            array $args = [], 
            $instanceName = 'default', 
            $beforeBuildBootstrapMethod = null,
            $afterBuildBootstrapMethod = null
    )
    {
        if (!isset(static::$instances[static::$mockNamespace][$className][$instanceName])) {
            if ($beforeBuildBootstrapMethod !== null) {
                $bootstrap = Container::getBootstrap();
                if (!is_callable([$bootstrap, $beforeBuildBootstrapMethod])) {
                    throw new ArchException("Bootstrap method $beforeBuildBootstrapMethod() must be declared in your bootstrap file");
                }
                $bootstrap->$beforeBuildBootstrapMethod();
            }
            $class = new \ReflectionClass($className);
            if (!$class->isInstantiable()) {
                throw new ArchException('Class ' . $className . ' must be instanciable');
            }
            static::$instances[static::$mockNamespace][$className][$instanceName]
                = count($args)
                ? $class->newInstanceArgs($args)
                : $class->newInstance();
            if ($afterBuildBootstrapMethod !== null) {
                $bootstrap = Container::getBootstrap();
                if (!method_exists($bootstrap, $afterBuildBootstrapMethod)) {
                    throw new ArchException("Bootstrap method $afterBuildBootstrapMethod() must be declared in your bootstrap file");
                }
                $bootstrap->$afterBuildBootstrapMethod();
            }
        }
        return static::$instances[static::$mockNamespace][$className][$instanceName];
    }
    
    /**
     * Get all instances in the container
     * @return array
     */
    public static function getInstances()
    {
        return array_key_exists(static::$mockNamespace, static::$instances) ? static::$instances[static::$mockNamespace] : [];
    }
    
    /**
     * Bind to stream->ucfirst if installed
     * @param string $txt
     * @return string
     */
    protected static function ucFirst(string $txt): string
    {
        if (class_exists('\Osf\Stream\Text')) {
            return \Osf\Stream\Text::ucFirst($txt);
        }
        return ucfirst($txt);
    }
}
