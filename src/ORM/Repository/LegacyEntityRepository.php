<?php

/*
 * Copyright 2018-2019 Q.One Technologies GmbH, Essen
 * This file is part of QOnePrivacyBundle.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace QOne\PrivacyBundle\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnexpectedResultException;
use QOne\PrivacyBundle\Mapping\GroupMetadataInterface;
use QOne\PrivacyBundle\ORM\Entity\Legacy;
use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Persistence\Object\LegacyInterface;
use QOne\PrivacyBundle\Persistence\Repository\LegacyRepositoryInterface;

/**
 * Class LegacyEntityRepository.
 */
class LegacyEntityRepository extends EntityRepository implements LegacyRepositoryInterface
{
    /**
     * @param AssetInterface         $asset
     * @param GroupMetadataInterface $groupMetadata
     *
     * @return LegacyInterface
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function leaveLegacy(AssetInterface $asset, GroupMetadataInterface $groupMetadata): LegacyInterface
    {
        $legacy = new Legacy(
            $asset->getUser(),
            $asset->getObject(),
            $groupMetadata->getFields(),
            $groupMetadata->getPolicy()
        );

        $this->_em->persist($legacy);
        $this->_em->flush($legacy);

        return $legacy;
    }

    /**
     * @param int $currentPosition
     *
     * @return LegacyInterface|null
     */
    public function findNextLegacy(int $currentPosition): ? LegacyInterface
    {
        $query = $this->createQueryBuilder('l')
            ->where('l.id > :currentPosition')
            ->setMaxResults(1)
            ->setParameters(compact('currentPosition'))
            ->getQuery();

        try {
            $result = $query->getSingleResult();
        } catch (UnexpectedResultException $e) {
            $result = null;
        }

        return $result;
    }
}
