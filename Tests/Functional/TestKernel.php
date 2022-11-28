<?php

namespace FOS\MessageBundle\Tests\Functional;

use FOS\MessageBundle\FOSMessageBundle;
use FOS\MessageBundle\Tests\Functional\Entity\Message;
use FOS\MessageBundle\Tests\Functional\Entity\Thread;
use FOS\MessageBundle\Tests\Functional\Entity\UserProvider;
use FOS\MessageBundle\Tests\Functional\EntityManager\MessageManager;
use FOS\MessageBundle\Tests\Functional\EntityManager\ThreadManager;
use FOS\MessageBundle\Tests\Functional\Form\UserToUsernameTransformer;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;
use function class_exists;

abstract class BaseTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return array(
            new FrameworkBundle(),
            new SecurityBundle(),
            new TwigBundle(),
            new FOSMessageBundle(),
        );
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', array(
            'secret' => 'MySecretKey',
            'test' => null,
            'form' => null,
            'http_method_override' => false,
        ));

        $security = array(
            'providers' => array('permissive' => array('id' => 'app.user_provider')),
            'firewalls' => array('main' => array('http_basic' => true)),
        );

        if (self::MAJOR_VERSION > 4) {
            $security['password_hashers'] = array('FOS\MessageBundle\Tests\Functional\Entity\User' => 'plaintext');
        } else {
            $security['encoders'] = array('FOS\MessageBundle\Tests\Functional\Entity\User' => 'plaintext');
        }
        $c->loadFromExtension('security', $security);

        $c->loadFromExtension('twig', array(
            'strict_variables' => '%kernel.debug%',
        ));

        $c->loadFromExtension('fos_message', array(
            'db_driver' => 'orm',
            'thread_class' => Thread::class,
            'message_class' => Message::class,
        ));

        $c->register('fos_user.user_to_username_transformer', UserToUsernameTransformer::class);
        $c->register('app.user_provider', UserProvider::class);
        $c->addCompilerPass(new RegisteringManagersPass());
    }
}

class RegisteringManagersPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container)
    {
        $container->register('fos_message.message_manager.default', MessageManager::class);
        $container->register('fos_message.thread_manager.default', ThreadManager::class);
    }
}

if (class_exists(RouteCollectionBuilder::class)) {
    class TestKernel extends BaseTestKernel {
        protected function configureRoutes(RouteCollectionBuilder $routes)
        {
            $routes->import('@FOSMessageBundle/Resources/config/routing.xml');
        }
    }
} else {
    class TestKernel extends BaseTestKernel {
        protected function configureRoutes(RoutingConfigurator $routes)
        {
            $routes->import('@FOSMessageBundle/Resources/config/routing.xml');
        }
    }
}