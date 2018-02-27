<?php

/*
 * This file is part of the OpenStates Framework (osf) package.
 * (c) Guillaume Ponçon <guillaume.poncon@openstates.com>
 * For the full copyright and license information, please read the LICENSE file distributed with the project.
 */

namespace Osf\Container;

use Osf\Container\AbstractContainer;
use Osf\Exception\ArchException;
use Osf\Application\Bootstrap;
use Osf\Controller\Router;
use Osf\Session\AppSession as Session;
use Osf\Cache\OsfCache as Cache;

/**
 * General OSF container (IOC)
 * 
 * This container manage each instance of many classes with 
 * some specific instanciation strategies. Do not worry about 
 * how to build an object, just use getXxx() ! 
 *
 * @author Guillaume Ponçon <guillaume.poncon@openstates.com>
 * @copyright OpenStates
 * @version 1.0
 * @since OSF-2.0 - 11 sept. 2013
 * @package osf
 * @subpackage container
 */
class OsfContainer extends AbstractContainer
{   
    protected static $instances = [];
    protected static $mockNamespace = self::MOCK_DISABLED;
    
    /**
     * @return \Osf\Application\OsfApplication
     */
    public static function getApplication(): \Osf\Application\OsfApplication
    {
        return self::buildObject('\Osf\Application\OsfApplication');
    }

    /**
     * @return \Osf\Controller\Request
     */
    public static function getRequest(): \Osf\Controller\Request
    {
        return self::buildObject('\Osf\Controller\Request');
    }

    /**
     * @return \Osf\Controller\Response
     */
    public static function getResponse(): \Osf\Controller\Response
    {
        return self::buildObject('\Osf\Controller\Response');
    }
    
    /**
     * @param string $appName
     * @return \Osf\Controller\Action
     */
    public static function getController($appName): \Osf\Controller\Action
    {
        $appName = self::ucFirst($appName);
        $class = "\\App\\" . $appName . '\Controller';
        if (defined('APPLICATION_PATH') && !is_dir(APPLICATION_PATH . '/App/' . $appName)) {
            throw new ArchException('Controller [' . $appName . '] not found', 404);
        }
        return self::buildObject($class, [], $appName);
    }
    
    /**
     * @return \Osf\View\OsfView
     */
    public static function getView(): \Osf\View\OsfView
    {
        return self::buildObject('\Osf\View\OsfView', [], 'view');
    }
    
    /**
     * @task [LAYOUT] voir à terme si afterBuildLayout est utile...
     * @return \Osf\View\OsfView
     */
    public static function getLayout(bool $callBootstrap = false): \Osf\View\OsfView
    {
        return self::buildObject('\Osf\View\OsfView', [], 'layout', null, $callBootstrap ? 'afterBuildLayout' : null);
    }
    
    /**
     * Get a view helpers object with a context
     * @param string|null|false $appName if null : common app. if false : Osf view helper
     * @param bool $layout
     * @return \Osf\View\Helper
     * @task [HELPERS] simplifier
     */
    public static function getViewHelper($appName = null, bool $layout = false)
    {
        if ($appName === false) {
            $class = '\Osf\View\Helper';
            $instance = 'osf';
        } else {
            $appName = $appName === null ? Router::getDefaultControllerName(true) : $appName;
            $class = "\\App\\" . $appName . '\View\Helper';
            $instance = $appName;
        }
        $viewName = $layout ? 'layout' : 'view';
        $instance .= $layout ? '_l' : '_v';
        return self::buildObject($class, [$viewName]); //, $instance);
    }
    
    /**
     * Get a view helpers object with layout namespace
     * @return \Osf\View\Helper
     */
    public static function getViewHelperLayout($appName = null)
    {
        return self::getViewHelper($appName, true);
    }
    
    /**
     * @return \Osf\Controller\Router
     */
    public static function getRouter(): \Osf\Controller\Router
    {
        $controller = self::ucFirst(OsfContainer::getRequest()->getController());
        if (!file_exists(APPLICATION_PATH . '/App/' . $controller . '/Config/Router.php')) {
            $controller = Router::getDefaultControllerName(true);
        }
        $routerClass = "App\\" . $controller . '\Router';
        $routerParams = self::getConfig()->getConfig('router');
        if (!is_array($routerParams)) {
            $routerParams = [];
        }
        
        return self::buildObject($routerClass, [$routerParams]);
    }
    
    /**
     * @task [BOOTSTRAP] FR: Optimiser la recherche du bootstrap
     * @return \Osf\Application\Bootstrap
     */
    public static function getBootstrap(): \Osf\Application\Bootstrap
    {
        $controller = self::ucFirst(self::getRequest()->getController());
        $appBootstrap = APPLICATION_PATH . '/App/' . $controller . '/Bootstrap.php';
        $controller = $controller === null || !file_exists($appBootstrap) 
                    ? Router::getDefaultControllerName(true) : $controller;
        $obj = self::buildObject("\\App\\" . $controller . '\Bootstrap', [], $controller);
        if (!($obj instanceof Bootstrap)) {
            throw new ArchException('Your bootstrap [' . $controller . '] must extends \Osf\Application\Bootstrap');
        }
        return $obj;
    }
    
    /**
     * Get the current application configuration
     * @return \Osf\Application\Config
     */
    public static function getConfig(): \Osf\Application\Config
    {
        return self::buildObject('\Osf\Application\Config');
    }
    
    /**
     * Get Osf Crypt object. Parameters are usefull ONLY for the first call
     * @param string $cryptKey
     * @param string $mode
     * @return \Osf\Crypt\Crypt
     */
    public static function getCrypt($cryptKey = Crypt::DEFAULT_KEY, $mode = Crypt::MODE_ASCII): \Osf\Crypt\Crypt
    {
        return self::buildObject('\Osf\Crypt\Crypt', [$cryptKey, $mode]);
    }
    
    /**
     * @return \Osf\Navigation\Menus
     */
    public static function getNavigationMenus(): \Osf\Navigation\Menus
    {
        return self::buildObject('\Osf\Navigation\Menus');
    }
    
    /**
     * @param string $menu
     * @return \Osf\Navigation\Item
     */
    public static function getNavigationMenu($menu = 'main'): \Osf\Navigation\Item
    {
        return self::getNavigationMenus()->getNavigation($menu);
    }
    
    /**
     * @param string $content
     * @return \Osf\DocMaker\DocMaker
     */
    public static function getDocMaker($content = null): \Osf\DocMaker\DocMaker
    {
        $docMaker = self::buildObject('\Osf\DocMaker\DocMaker');
        if ($content !== null) {
            $docMaker->setContent($content);
        }
        return $docMaker;
    }
    
    /**
     * @return \Osf\DocMaker\Markdown
     */
    public static function getMarkdown(): \Osf\DocMaker\Markdown
    {
        return self::buildObject('\Osf\DocMaker\Markdown');
    }
    
    /**
     * @return \Osf\Device\MobileDetect
     */
    public static function getDevice(): \Osf\Device\MobileDetect
    {
        return self::buildObject('\Osf\Device\MobileDetect');
    }
    
    
    /**
     * Get a session objet
     * @param string $namespace
     * @return \Osf\Session\AppSession
     */
    public static function getSession(string $namespace = Session::DEFAULT_NAMESPACE): \Osf\Session\AppSession
    {
        return self::buildObject('\Osf\Session\AppSession', [$namespace], $namespace);
    }
    
    /**
     * @param string $namespace
     * @return \Osf\Cache\OsfCache
     */
    public static function getCache(string $namespace = Cache::DEFAULT_NAMESPACE): \Osf\Cache\OsfCache
    {
        return self::buildObject('\Osf\Cache\OsfCache', [$namespace], $namespace);
    }
    
    /**
     * @return \Osf\Application\Locale
     */
    public static function getLocale(): \Osf\Application\Locale
    {
        return self::buildObject('\Osf\Application\Locale');
    }
}
