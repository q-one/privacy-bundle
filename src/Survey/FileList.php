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

/**
 * Class FileList.
 */
class FileList implements /*\IteratorAggregate,*/ FileListInterface
{
    /** @var FileInterface[] */
    private $files = [];

    /**
     * {@inheritdoc}
     */
    /*public function getIterator()
    {
        return new \ArrayIterator($this->files);
    }*/

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): FileInterface
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->add($value);
        } else {
            $this->set($offset, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->files);
    }

    /**
     * {@inheritdoc}
     */
    public function add(FileInterface $file): void
    {
        foreach ($this->files as $currentFile) {
            if ($currentFile->getObject()->equals($file->getObject())) {
                $currentFile->addGroups($file->getGroups());

                return;
            }
        }

        $this->files[] = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function addAll(FileListInterface $otherList): void
    {
        foreach ($otherList->getFiles() as $file) {
            $this->add($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(int $offset): FileInterface
    {
        if (!isset($this->files[$offset])) {
            throw new \OutOfBoundsException(sprintf('The offset "%s" does not exist.', $offset));
        }

        return $this->files[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function has(int $offset): bool
    {
        return isset($this->files[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function set(int $offset, FileInterface $file): void
    {
        $this->files[$offset] = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(int $offset): void
    {
        unset($this->files[$offset]);
    }

    /**
     * @return FileInterface[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param FileInterface[] $files
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }
}
