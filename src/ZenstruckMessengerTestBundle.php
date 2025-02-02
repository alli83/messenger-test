<?php

namespace Zenstruck\Messenger\Test;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zenstruck\Messenger\Test\Transport\TestTransportFactory;
use Zenstruck\Messenger\Test\Transport\TestTransportRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ZenstruckMessengerTestBundle extends Bundle implements CompilerPassInterface
{
    public function build(ContainerBuilder $container): void
    {
        $container->register('zenstruck_messenger_test.transport_factory', TestTransportFactory::class)
            ->setArguments([new Reference('messenger.routable_message_bus'), new Reference('event_dispatcher')])
            ->addTag('messenger.transport_factory')
        ;

        $container->register('zenstruck_messenger_test.transport_registry', TestTransportRegistry::class)
            ->setPublic(true)
        ;

        $container->addCompilerPass($this);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return null;
    }

    public function process(ContainerBuilder $container): void
    {
        $registry = $container->getDefinition('zenstruck_messenger_test.transport_registry');

        foreach ($container->findTaggedServiceIds('messenger.receiver') as $id => $tags) {
            $name = $id;

            foreach ($tags as $tag) {
                if (isset($tag['alias'])) {
                    $name = $tag['alias'];
                }
            }

            $registry->addMethodCall('register', [$name, new Reference($id)]);
        }
    }
}
