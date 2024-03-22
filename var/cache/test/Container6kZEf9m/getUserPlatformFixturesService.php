<?php

namespace Container6kZEf9m;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getUserPlatformFixturesService extends App_KernelTestDebugContainer
{
    /**
     * Gets the public 'App\DataFixtures\UserPlatformFixtures' shared autowired service.
     *
     * @return \App\DataFixtures\UserPlatformFixtures
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/vendor/doctrine/data-fixtures/src/FixtureInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/doctrine/data-fixtures/src/SharedFixtureInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/doctrine/data-fixtures/src/AbstractFixture.php';
        include_once \dirname(__DIR__, 4).'/vendor/doctrine/doctrine-fixtures-bundle/ORMFixtureInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/doctrine/doctrine-fixtures-bundle/Fixture.php';
        include_once \dirname(__DIR__, 4).'/src/DataFixtures/UserPlatformFixtures.php';

        return $container->services['App\\DataFixtures\\UserPlatformFixtures'] = new \App\DataFixtures\UserPlatformFixtures(($container->privates['security.user_password_hasher'] ?? $container->load('getSecurity_UserPasswordHasherService')));
    }
}
