<?php

namespace Container6kZEf9m;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getPHPCRDatabaseToolService extends App_KernelTestDebugContainer
{
    /**
     * Gets the private 'Liip\TestFixturesBundle\Services\DatabaseTools\PHPCRDatabaseTool' shared service.
     *
     * @return \Liip\TestFixturesBundle\Services\DatabaseTools\PHPCRDatabaseTool
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/vendor/liip/test-fixtures-bundle/src/Services/DatabaseTools/AbstractDatabaseTool.php';
        include_once \dirname(__DIR__, 4).'/vendor/liip/test-fixtures-bundle/src/Services/DatabaseTools/PHPCRDatabaseTool.php';

        return $container->privates['Liip\\TestFixturesBundle\\Services\\DatabaseTools\\PHPCRDatabaseTool'] = new \Liip\TestFixturesBundle\Services\DatabaseTools\PHPCRDatabaseTool($container, ($container->services['Liip\\TestFixturesBundle\\Services\\FixturesLoaderFactory'] ?? $container->load('getFixturesLoaderFactoryService')));
    }
}
