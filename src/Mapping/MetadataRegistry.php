<?php

/*
 * Copyright (c) 2018-2019 Q.One Technologies GmbH, Essen
 * All rights reserved.
 *
 * This file is part of CloudBasket.
 *
 * NOTICE: The contents of this file are CONFIDENTIAL and MUST NOT be published
 * nor redistributed without prior written permission.
 */

namespace QOne\PrivacyBundle\Mapping;

use Psr\Cache\InvalidArgumentException;
use QOne\PrivacyBundle\Exception\NoSuchMetadataException;
use QOne\PrivacyBundle\Mapping\Loader\LoaderInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Class MetadataRegistry.
 */
class MetadataRegistry implements MetadataRegistryInterface
{
    /**
     * @var TagAwareAdapterInterface|null
     */
    protected $cache;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    public function __construct(LoaderInterface $loader)
    {
        $this->setLoader($loader);
    }

    /**
     * @param TagAwareAdapterInterface $cache
     */
    public function setCache(?TagAwareAdapterInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @param LoaderInterface $loader
     */
    public function setLoader(LoaderInterface $loader): void
    {
        $this->loader = $loader;
    }

    /**
     * @param string $className
     *
     * @return ClassMetadataInterface
     *
     * @throws \Exception
     */
    public function getMetadataFor(string $className): ClassMetadataInterface
    {
        try {
            $cacheItem = $this->cache->getItem(self::getCacheKey($className));

            $data = $cacheItem->get();
            $classMetadata = unserialize($data);
            if (!$classMetadata instanceof ClassMetadataInterface) {
                throw new \Exception();
            }

            return $classMetadata;
        } catch (InvalidArgumentException $e) {
            throw new \RuntimeException(sprintf('Error on getting cache for class %s', $className), null, $e);
        } catch (\Exception $exception) {
            throw new \RuntimeException(
                sprintf('ClassMeta "%s" is not instance of ClassMetadataInterface', $className)
            );
        }
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    public function hasMetadataFor(string $className): bool
    {
        //return @$this->cache->hasItem(self::getCacheKey($className)) or false; // TODO :-(

        try {
            return $this->cache->hasItem(self::getCacheKey($className));
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Load / cache metadata of a single class.
     *
     * @param string $className
     *
     * @throws NoSuchMetadataException
     */
    public function loadMetadataFor(string $className): void
    {
        $classMetaData = new ClassMetadata($className);
        if (!$this->loader->loadMetadata($className, $classMetaData)) {
            throw new NoSuchMetadataException(
                sprintf('Given class %s has no privacy-related metadata attached', $className)
            );
        }

        try {
            $cacheItem = $this->cache->getItem(self::getCacheKey($className));
            $data = serialize($classMetaData);
            $cacheItem->set($data);
            $cacheItem->tag(['qone_privacy_cache']); // TODO conf
            $this->cache->save($cacheItem);
        } catch (\Psr\Cache\CacheException $e) {
            throw new \RuntimeException(sprintf('Error on saving cache item for class %s', $className), null, $e);
        }
    }

    /**
     * @param string $className
     */
    public function pruneMetadataFor(string $className): void
    {
        try {
            $this->cache->deleteItem(self::getCacheKey($className));
        } catch (InvalidArgumentException $e) {
            throw new \RuntimeException(sprintf('Error on pruning cache for class %s', $className), null, $e);
        }
    }

    /**
     * @return array
     */
    public function getLoadableClassNames(): array
    {
        return $this->loader->getLoadableClassNames();
    }

    /**
     * @param string $className
     *
     * @return string
     */
    private static function getCacheKey(string $className): string
    {
        return strtr($className, ['\\' => '.']);
    }
}
