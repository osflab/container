<?php

/*
 * This file is part of the OpenStates Framework (osf) package.
 * (c) Guillaume Ponçon <guillaume.poncon@openstates.com>
 * For the full copyright and license information, please read the LICENSE file distributed with the project.
 */

namespace Osf\Container\ZendContainer;

use Osf\Container\ZendContainer;
use Osf\Test\Runner as OsfTest;

/**
 * Zend container unit tests
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
        
        if (class_exists('Zend\I18n\Translator\Translator')) {
            self::assert(extension_loaded('intl'), 'intl extension for Zend Translator is not loaded');
            $translator = ZendContainer::getTranslate(false);
            self::assert($translator instanceof \Zend\I18n\Translator\Translator, 'Translator not found');
        }
        
        return self::getResult();
    }
}
