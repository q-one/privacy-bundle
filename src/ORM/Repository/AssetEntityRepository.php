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

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\TransactionRequiredException;
use Doctrine\ORM\UnexpectedResultException;
use QOne\PrivacyBundle\ORM\Entity\EntityReference;
use QOne\PrivacyBundle\Persistence\Object\AssetInterface;
use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Persistence\Repository\AssetRepositoryInterface;

/**
 * Class AssetEntityRepository.
 */
class AssetEntityRepository extends EntityRepository implements AssetRepositoryInterface
{
    /**
     * @param ObjectReferenceInterface $user
     *
     * @return array
     */
    public function findAssetsForUser(ObjectReferenceInterface $user): array
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.user.className = :userClassName AND a.user.identifier = :userIdentifier')
            ->setParameters([
                'userClassName' => EntityReference::createSearchKey($user->getClassName()),
                'userIdentifier' => EntityReference::createSearchKey($user->getIdentifier()),
            ])
            ->getQuery();

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAsset(
        ObjectReferenceInterface $user,
        ObjectReferenceInterface $object,
        string $groupId
    ): ?AssetInterface {
        $query = $this->createQueryBuilder('a')
            ->where('a.user.className = :userClassName AND a.user.identifier = :userIdentifier')
            ->andWhere('a.object.className = :objectClassName AND a.object.identifier = :objectIdentifier')
            ->andWhere('a.groupId = :groupId')
            ->setParameters([
                'userClassName' => EntityReference::createSearchKey($user->getClassName()),
                'userIdentifier' => EntityReference::createSearchKey($user->getIdentifier()),
                'objectClassName' => EntityReference::createSearchKey($object->getClassName()),
                'objectIdentifier' => EntityReference::createSearchKey($object->getIdentifier()),
                'groupId' => $groupId,
            ])
            ->getQuery();

        try {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
            $result = $query->getSingleResult(Query::HYDRATE_OBJECT);
        } catch (TransactionRequiredException | UnexpectedResultException $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findAssetForObjectGroup(
        ObjectReferenceInterface $object,
        string $groupId
    ): ?AssetInterface {
        $query = $this->createQueryBuilder('a')
            ->andWhere('a.object.className = :objectClassName AND a.object.identifier = :objectIdentifier')
            ->andWhere('a.groupId = :groupId')
            ->setParameters([
                'objectClassName' => EntityReference::createSearchKey($object->getClassName()),
                'objectIdentifier' => EntityReference::createSearchKey($object->getIdentifier()),
                'groupId' => $groupId,
            ])
            ->getQuery();

        try {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
            $result = $query->getSingleResult(Query::HYDRATE_OBJECT);
        } catch (TransactionRequiredException | UnexpectedResultException $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findNextValidationAsset(?\DateInterval $validationInterval): ?AssetInterface
    {
        try {
            // who was it to let DateTime* classes throw an \Exception (why in the world ???)
            // in its constructor?!
            $now = $reevaluation = new \DateTimeImmutable();

            if (null !== $validationInterval) {
                $reevaluation = $now->sub($validationInterval);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Couldn\'t determine the current date', 0, $e);
        }

        $query = $this->createQueryBuilder('a')
            ->where('a.evaluationValidatedAt IS NULL OR a.evaluationValidatedAt < :reevaluation')
            ->andWhere('a.evaluationPredictedAt IS NULL OR a.evaluationPredictedAt < :now')
            ->orderBy('a.evaluationValidatedAt', 'ASC')
            ->setMaxResults(1)
            ->setParameters(compact('now', 'reevaluation'))
            ->getQuery()
        ;

        try {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
            $result = $query->getSingleResult(Query::HYDRATE_OBJECT);
        } catch (TransactionRequiredException | UnexpectedResultException $e) {
            $result = null;
        }

        return $result;
    }
}
