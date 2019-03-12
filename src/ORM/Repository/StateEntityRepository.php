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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use QOne\PrivacyBundle\ORM\Entity\State;
use QOne\PrivacyBundle\Persistence\Object\StateInterface;
use QOne\PrivacyBundle\Persistence\Repository\StateRepositoryInterface;

class StateEntityRepository extends EntityRepository implements StateRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIntValue(string $key): ?int
    {
        return $this->getOrCreateState($key)->getIntValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setIntValue(string $key, ?int $value, bool $delta = false): void
    {
        $this->getOrCreateState($key, function ($em, $state) use ($value, $delta) {
            /** @var EntityManager $em */
            /** @var StateInterface $state */
            $nextValue = $value;

            if ($delta && null !== $value) {
                $nextValue += ($state->getIntValue() ?? 0);
            }

            $state->setIntValue($nextValue);
            $em->persist($state);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getStringValue(string $key): ?string
    {
        return $this->getOrCreateState($key)->getStringValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setStringValue(string $key, ?string $value): void
    {
        $this->getOrCreateState($key, function ($em, $state) use ($value) {
            /* @var EntityManager $em */
            /* @var StateInterface $state */
            $state->setStringValue($value);
            $em->persist($state);
        });
    }

    /**
     * @param string   $key
     * @param callable $callback
     *
     * @return StateInterface
     */
    protected function getOrCreateState(string $key, ?callable $callback = null): StateInterface
    {
        try {
            return $this->_em->transactional(
                function ($em) use ($key, $callback) {
                    /** @var EntityManager $em */
                    if (null === $state = $em->find(State::class, $key, LockMode::PESSIMISTIC_WRITE)) {
                        $state = new State($key);
                        $em->persist($state);
                    }

                    if (null !== $callback) {
                        $callback($em, $state);
                    }

                    return $state;
                }
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf(
                'Couldn\'t neither get nor create state with key "%s"; mustn\'t happen!',
                $key
            ));
        }
    }
}
