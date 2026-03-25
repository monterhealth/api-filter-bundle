<?php

namespace ContainerDXSpbME;
include_once \dirname(__DIR__, 4).'/vendor/doctrine/persistence/src/Persistence/ObjectManager.php';
include_once \dirname(__DIR__, 4).'/vendor/doctrine/orm/src/EntityManagerInterface.php';
include_once \dirname(__DIR__, 4).'/vendor/doctrine/orm/src/EntityManager.php';

class EntityManagerGhost75fcd00 extends \Doctrine\ORM\EntityManager implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".parent::class."\0".'cache' => [parent::class, 'cache', null, 16],
        "\0".parent::class."\0".'closed' => [parent::class, 'closed', null, 16],
        "\0".parent::class."\0".'config' => [parent::class, 'config', null, 16],
        "\0".parent::class."\0".'conn' => [parent::class, 'conn', null, 16],
        "\0".parent::class."\0".'eventManager' => [parent::class, 'eventManager', null, 16],
        "\0".parent::class."\0".'expressionBuilder' => [parent::class, 'expressionBuilder', null, 16],
        "\0".parent::class."\0".'filterCollection' => [parent::class, 'filterCollection', null, 16],
        "\0".parent::class."\0".'metadataFactory' => [parent::class, 'metadataFactory', null, 16],
        "\0".parent::class."\0".'proxyFactory' => [parent::class, 'proxyFactory', null, 16],
        "\0".parent::class."\0".'repositoryFactory' => [parent::class, 'repositoryFactory', null, 16],
        "\0".parent::class."\0".'unitOfWork' => [parent::class, 'unitOfWork', null, 16],
        'cache' => [parent::class, 'cache', null, 16],
        'closed' => [parent::class, 'closed', null, 16],
        'config' => [parent::class, 'config', null, 16],
        'conn' => [parent::class, 'conn', null, 16],
        'eventManager' => [parent::class, 'eventManager', null, 16],
        'expressionBuilder' => [parent::class, 'expressionBuilder', null, 16],
        'filterCollection' => [parent::class, 'filterCollection', null, 16],
        'metadataFactory' => [parent::class, 'metadataFactory', null, 16],
        'proxyFactory' => [parent::class, 'proxyFactory', null, 16],
        'repositoryFactory' => [parent::class, 'repositoryFactory', null, 16],
        'unitOfWork' => [parent::class, 'unitOfWork', null, 16],
    ];
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('EntityManagerGhost75fcd00', false)) {
    \class_alias(__NAMESPACE__.'\\EntityManagerGhost75fcd00', 'EntityManagerGhost75fcd00', false);
}
