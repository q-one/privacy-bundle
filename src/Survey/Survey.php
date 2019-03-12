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

use QOne\PrivacyBundle\Persistence\Object\ObjectReferenceInterface;
use QOne\PrivacyBundle\Persistence\Object\SimpleReference;

class Survey implements SurveyInterface
{
    /**
     * @var ObjectReferenceInterface
     */
    private $user;

    /**
     * @var FileListInterface
     */
    private $files;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * Survey constructor.
     *
     * @param ObjectReferenceInterface $user
     * @param FileListInterface        $files
     * @param \DateTimeInterface       $createdAt
     *
     * @throws \Exception
     */
    public function __construct(ObjectReferenceInterface $user, FileListInterface $files = null, \DateTimeInterface $createdAt = null)
    {
        $this->user = SimpleReference::fromReference($user);
        $this->files = $files ?: new FileList();
        $this->createdAt = $createdAt ?: new \DateTimeImmutable();
    }

    /**
     * @return ObjectReferenceInterface
     */
    public function getUser(): ObjectReferenceInterface
    {
        return $this->user;
    }

    /**
     * @return FileListInterface
     */
    public function getFiles(): FileListInterface
    {
        return $this->files;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
