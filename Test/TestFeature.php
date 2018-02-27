<?php

/*
 * This file is part of the OpenStates Framework (osf) package.
 * (c) Guillaume Ponçon <guillaume.poncon@openstates.com>
 * For the full copyright and license information, please read the LICENSE file distributed with the project.
 */

namespace Osf\Container\Test;

/**
 * A fictive feature to test container
 *
 * @author Guillaume Ponçon <guillaume.poncon@openstates.com>
 * @copyright OpenStates
 * @version 1.0
 * @since OSF-3.0.0 - 2018
 * @package osf
 * @subpackage container
 */
class TestFeature
{
    protected static $instanceCounter = 0;
    protected $instanceNumber;
    protected $name;
    
    public function __construct(?string $name = null)
    {
        $this->name = $name;
        $this->instanceNumber = ++self::$instanceCounter;
    }
    
    public function getInstanceNumber(): int
    {
        return $this->instanceNumber;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(string $name)
    {
        $this->name = $name;
    }
}
