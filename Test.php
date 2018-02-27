<?php

/*
 * This file is part of the OpenStates Framework (osf) package.
 * (c) Guillaume Ponçon <guillaume.poncon@openstates.com>
 * For the full copyright and license information, please read the LICENSE file distributed with the project.
 */

namespace Osf\Container;

use Osf\Container\Test\TestContainer as Container;
use Osf\Container\Test\TestFeature;
use Osf\Container\VendorContainer;
use Osf\Test\Runner as OsfTest;

/**
 * Test class for container feature
 *
 * @author Guillaume Ponçon <guillaume.poncon@openstates.com>
 * @copyright OpenStates
 * @version 1.0
 * @since OSF-2.0 - 11 sept. 2013
 * @package osf
 * @subpackage test
 */
class Test extends OsfTest
{
    public static function run()
    {
        self::reset();
        $obj = Container::buildObject('\stdClass');
        self::assert(is_object($obj), 'Obj is not an object');
        self::assert($obj instanceof \stdClass, 'Obj is not a stdclass instance');
        $obj2 = Container::buildObject('\stdClass', array(), 'new');
        self::assert($obj2 instanceof \stdClass, 'Obj2 is not a stdclass instance');
        self::assert($obj2 !== $obj, 'Obj2 and Obj is the same instance');
        $feature = Container::getFeature();
        self::assert($feature instanceof TestFeature, 'Object builded wrong');
        self::assertEqual($feature->getName(), null);
        self::assertEqual($feature->getInstanceNumber(), 1);
        self::assertEqual(Container::getFeature()->getInstanceNumber(), 1);
        self::assertEqual(Container::getFeature(null, 'toto')->getInstanceNumber(), 1);
        self::assertEqual(Container::getFeature(null, 'toto')->getName(), null);
        self::assertEqual(Container::getFeature('OTHER', 'toto')->getName(), 'toto');
        self::assertEqual(Container::getFeature('OTHER', null)->getName(), 'toto');
        self::assertEqual(Container::getFeature('OTHER', null)->getInstanceNumber(), 2);
        $instances = Container::getInstances();
        self::assert(isset($instances['\Osf\Container\Test\TestFeature']['default']), 'An instance not found in container');
        self::assert(isset($instances['\stdClass']['default']), 'An instance not found in container');
        self::assert(isset($instances['\stdClass']['new']), 'An instance not found in container');
        Container::getFeature()->setName('real');
        self::assertEqual(Container::getFeature()->getName(), 'real');
        $mockObj = new TestFeature('mock');
        Container::cleanMocks();
        Container::registerMock($mockObj, '\Osf\Container\Test\TestFeature');
        self::assert(Container::getFeature() instanceof TestFeature);
        Container::setMockNamespace(Container::MOCK_ENABLED);
        self::assertEqual(Container::getFeature(), $mockObj);
        self::assertEqual(Container::getFeature()->getName(), 'mock');
        Container::cleanMocks();
        self::assertEqual(Container::getFeature()->getName(), null);
        Container::setMockNamespace(Container::MOCK_DISABLED);
        self::assertEqual(Container::getFeature()->getName(), 'real');
        if (class_exists('Twig_TemplateWrapper')) {
            $string = 'Bonjour {{ contact.nom }}, ça va ?';
            $data = ['contact' => ['nom' => "Guillaume"]];
            $twig = VendorContainer::newTwig($string, null, false);
            self::assert($twig instanceof \Twig_TemplateWrapper);
            self::assert($twig->render($data) === 'Bonjour Guillaume, ça va ?');
        }
        
        return self::getResult();
    }
}
