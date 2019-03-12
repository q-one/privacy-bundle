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

namespace QOne\PrivacyBundle\Survey;

use QOne\PrivacyBundle\Condition\ConditionInterface;

class Group implements GroupInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var array
     */
    private $conditions;

    /**
     * @var RecordInterface[]
     */
    private $records;

    /**
     * @var string|null
     */
    private $source;

    /**
     * Group constructor.
     *
     * @param string $id
     * @param array $conditions
     * @param array $records
     * @param string|null $source
     */
    public function __construct(string $id, array $conditions, array $records, ?string $source)
    {
        $this->id = $id;
        $this->conditions = [];
        $this->records = [];
        $this->source = $source;

        foreach ($conditions as $condition) {
            /*if (!$condition instanceof ConditionInterface) {
                throw new \InvalidArgumentException(sprintf('Expected $conditions to be an array of ConditionInterface, but got %s', get_class($condition)));
            }*/

            if (is_array($condition)) {
                $this->conditions[] = $condition;
                continue;
            }

            $message = preg_replace_callback(
                '/(\{(?<param>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\})/i',
                function ($m) use ($condition) {
                    return $condition->getMessageParameters()[$m['param']] ?? $m[0];
                },
                $condition->getMessageTemplate()
            );

            $this->conditions[] = [
                'type' => get_class($condition),
                'message' => $message,
                'messageTemplate' => $condition->getMessageTemplate(),
                'messageParameters' => $condition->getMessageParameters(),
            ];
        }

        foreach ($records as $record) {
            if (!$record instanceof RecordInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Expected $records to be an array of RecordInterface, but got %s', get_class($record))
                );
            }

            $this->records[] = $record;
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @return RecordInterface[]
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }
}
