<?php

/*
 * This file is part of the OpenStates Framework (osf) package.
 * (c) Guillaume Ponçon <guillaume.poncon@openstates.com>
 * For the full copyright and license information, please read the LICENSE file distributed with the project.
 */

namespace Osf\Container\Test;

use Osf\Container\AbstractContainer;

/**
 * A test container for unit tests
 *
 * @author Guillaume Ponçon <guillaume.poncon@openstates.com>
 * @copyright OpenStates
 * @version 1.0
 * @since OSF-3.0.0 - 2018
 * @package osf
 * @subpackage container
 */
class TestContainer extends AbstractContainer
{
    protected static $instances = [];
    protected static $mockNamespace = self::MOCK_DISABLED;
    
    /**
     * @task [BOOTSTRAP] FR: Optimiser la recherche du bootstrap
     * @return TestFeature
     */
    public static function getFeature(?string $namespace = null, ?string $arg = null): TestFeature
    {
        return self::buildObject('\Osf\Container\Test\TestFeature', [$arg], $namespace ?? 'default');
    }
}
