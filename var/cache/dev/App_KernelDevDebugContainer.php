<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\ContainerXmY5Gfg\App_KernelDevDebugContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/ContainerXmY5Gfg/App_KernelDevDebugContainer.php') {
    touch(__DIR__.'/ContainerXmY5Gfg.legacy');

    return;
}

if (!\class_exists(App_KernelDevDebugContainer::class, false)) {
    \class_alias(\ContainerXmY5Gfg\App_KernelDevDebugContainer::class, App_KernelDevDebugContainer::class, false);
}

return new \ContainerXmY5Gfg\App_KernelDevDebugContainer([
    'container.build_hash' => 'XmY5Gfg',
    'container.build_id' => 'a0957694',
    'container.build_time' => 1710777559,
    'container.runtime_mode' => \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) ? 'web=0' : 'web=1',
], __DIR__.\DIRECTORY_SEPARATOR.'ContainerXmY5Gfg');
