<?php

/*
 * This file is part of the OpenStates Framework (osf) package.
 * (c) Guillaume Ponçon <guillaume.poncon@openstates.com>
 * For the full copyright and license information, please read the LICENSE file distributed with the project.
 */

namespace Osf\Container\VendorContainer\Twig;

use Osf\Test\Runner as OsfTest;
use Osf\Container\VendorContainer;
use Osf\Crypt\Crypt;
use Twig_TemplateWrapper as Twig;

/**
 * Twit proxy unit test
 *
 * @author Guillaume Ponçon <guillaume.poncon@openstates.com>
 * @copyright OpenStates
 * @version 1.0
 * @since OSF-2.0 - 2017
 * @package osf
 * @subpackage test
 */
class Test extends OsfTest
{
    public static function run()
    {
        self::reset();
        
        if (class_exists('Twig_TemplateWrapper')) {
            $template = 'Bonjour {{contact.nom}}, vous êtes <b>beau</b> et avez {{contact.age}} ans.';
            $values = ['contact' => ['nom' => 'Guillaume <b>Ponçon</b>', 'age' => 30]];
            $expected = 'Bonjour Guillaume <b>Ponçon</b>, vous êtes <b>beau</b> et avez 30 ans.';
            $twig = VendorContainer::newTwig($template);
            self::assert($twig instanceof Twig);
            $result = $twig->render($values);
            self::assertEqual($result, $expected);
            for ($i = 0; $i <= 10; $i++) {
                for ($j = 0; $j <= 100; $j++) {
                    $values[Crypt::hash($i)][Crypt::hash($j)] = 'test';
                }
            }

            $template = str_repeat($template, 500);
            $expected = str_repeat($expected, 500);
            $time = microtime(true);
            $twig = VendorContainer::newTwig($template);
            $result = $twig->render($values);
            self::assertEqual($result, $expected);
            $duration = round((microtime(true) - $time) * 1000);
            self::assert($duration < 200, 'Slow compilation detected');
            $template = 'Bonjour {{contact.nom|upper}}, vous êtes <b>beau</b> et avez {{contact.age}} ans.';
            $values = ['contact' => ['nom' => 'Guillaume <b>Ponçon</b>', 'age' => 30]];
            $expected = 'Bonjour GUILLAUME <B>PONÇON</B>, vous êtes <b>beau</b> et avez 30 ans.';
            $result = VendorContainer::newTwig($template)->render($values);
            self::assertEqual($result, $expected);
            $template = "{% include 'test.html' %}";
            try {
                $result = VendorContainer::newTwig($template)->render($values);
                self::assert(false, 'Include doit être interdit...');
            } catch (\Twig_Sandbox_SecurityNotAllowedTagError $e) {
            } catch (\Exception $e) {
                self::assert(false, 'Mauvaise exception [' . get_class($e) . ']: ' . $e->getMessage());
            }
            $template = "{{ contact.nom|raw }}";
            try {
                $result = VendorContainer::newTwig($template)->render($values);
                self::assert(false, 'Le filtre raw doit être interdit...');
            } catch (\Twig_Sandbox_SecurityNotAllowedFilterError $e) {
            } catch (\Exception $e) {
                self::assert(false, 'Mauvaise exception [' . get_class($e) . ']: ' . $e->getMessage());
            }
        }
        
        return self::getResult();
    }
}
