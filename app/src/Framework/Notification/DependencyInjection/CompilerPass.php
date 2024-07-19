<?php

declare(strict_types=1);

namespace App\Framework\Notification\DependencyInjection;

use App\Framework\Notification\Handler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(Handler::class)) {
            return;
        }
        $definition = $container->findDefinition(Handler::class);
        foreach ($container->findTaggedServiceIds('app.notification.handler_strategy') as $id => $tags) {
            $definition->addMethodCall('addStrategy', [new Reference($id)]);
        }
    }
}
