<?php

namespace ContainerXmY5Gfg;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getUserControllerService extends App_KernelDevDebugContainer
{
    /**
     * Gets the public 'App\Controller\Platform\UserController' shared autowired service.
     *
     * @return \App\Controller\Platform\UserController
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/vendor/symfony/framework-bundle/Controller/AbstractController.php';
        include_once \dirname(__DIR__, 4).'/src/Controller/Platform/UserController.php';
        include_once \dirname(__DIR__, 4).'/src/Service/Platform/UserService.php';

        $container->services['App\\Controller\\Platform\\UserController'] = $instance = new \App\Controller\Platform\UserController(new \App\Service\Platform\UserService(($container->services['doctrine.orm.default_entity_manager'] ?? self::getDoctrine_Orm_DefaultEntityManagerService($container))), ($container->privates['security.helper'] ?? $container->load('getSecurity_HelperService')));

        $instance->setContainer(($container->privates['.service_locator.O2p6Lk7'] ?? $container->load('get_ServiceLocator_O2p6Lk7Service'))->withContext('App\\Controller\\Platform\\UserController', $container));

        return $instance;
    }
}
