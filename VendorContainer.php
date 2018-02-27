<?php

/*
 * This file is part of the OpenStates Framework (osf) package.
 * (c) Guillaume Ponçon <guillaume.poncon@openstates.com>
 * For the full copyright and license information, please read the LICENSE file distributed with the project.
 */

namespace Osf\Container;

use Osf\Container\OsfContainer as Container;
use Osf\Exception\ArchException;
use Osf\Container\VendorContainer\Twig\TwigLoaderString;
use Redis;
use Twig_Environment as Twig;
use Twig_Sandbox_SecurityPolicy as SecurityPolicy;
use Twig_Extension_Sandbox as Sandbox;

/**
 * Container for external (vendor) features
 * 
 * @author Guillaume Ponçon <guillaume.poncon@openstates.com>
 * @copyright OpenStates
 * @version 1.0
 * @since OSF-2.0 - 24 sept. 2013
 * @package osf
 * @subpackage container
 */
class VendorContainer extends AbstractContainer
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 6379;
    const DEFAULT_CONFIG = [
        'host' => self::DEFAULT_HOST, 
        'port' => self::DEFAULT_PORT
    ];
    
    const TWIG_CACHE_DIR = '/var/cache/twig';
    
    protected static $instances = [];
    protected static $mockNamespace = self::MOCK_DISABLED;
    
    /**
     * @return \Redis
     */
    public static function getRedis(): Redis
    {
        /* @var $redis \Redis */
        $redis = self::buildObject('\Redis');
        if (!$redis->isConnected()) {
            $config = Container::getConfig()->getConfig('redis');
            $config = is_array($config) ? array_replace(self::DEFAULT_CONFIG, $config) : self::DEFAULT_CONFIG;
            if (!$redis->pconnect($config['host'], $config['port'])) {
                throw new ArchException('Unable to connect redis: ' . $redis->getLastError());
            }
            if (!array_key_exists('auth', $config) 
                    && isset($_SERVER['HOME']) 
                    && file_exists($_SERVER['HOME'] . '/.redispass')) {
                $config['auth'] = trim(file_get_contents($_SERVER['HOME'] . '/.redispass'));
            }
            if (array_key_exists('auth', $config) && $config['auth'] !== '') {
                if (!$redis->auth($config['auth'])) {
                    throw new ArchException('Unable to auth redis, bad auth string?');
                }
            }
            $serializer = defined('Redis::SERIALIZER_IGBINARY') 
                ? Redis::SERIALIZER_IGBINARY 
                : Redis::SERIALIZER_PHP;
            $redis->setOption(Redis::OPT_SERIALIZER, $serializer);
        }
        return $redis;
    }
    
    /**
     * Charge une instance de twig destinée à compiler une chaîne
     * @param string $content
     * @param string $name
     * @param bool $persist
     * @return \Twig_TemplateWrapper
     */
    public static function newTwig($content = null, $name = null, bool $persist = false)
    {
        $loader = new TwigLoaderString();
        $name = $loader->register($content, $name, $persist);
        $twig = (new Twig($loader, self::getTwigConfig()));
        $twig->addExtension(new Sandbox(self::buildSandboxPolicies()));
        return $twig->load($name);
    }
    
    /**
     * @return array
     */
    protected static function getTwigConfig(): array
    {
        $cacheDir = defined('APP_PATH') ? APP_PATH . self::TWIG_CACHE_DIR : sys_get_temp_dir();
        return [
            'cache' => $cacheDir,
            'autoescape' => false
        ];
    }
    
    /**
     * @return SecurityPolicy
     */
    protected static function buildSandboxPolicies(): SecurityPolicy
    {
        $tags = ['for', 'if', 'set'];
        $filters = [
            'abs', 'capitalize', 'default', 'lower', 'replace', 'round', 
            'split', 'trim', 'upper'
        ];
        $methods    = [];
        $properties = [];
        $functions  = [];
        return new SecurityPolicy($tags, $filters, $methods, $properties, $functions);
    }
}
