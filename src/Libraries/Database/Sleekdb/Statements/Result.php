<?php

/**
 * Quantum PHP Framework
 *
 * An open source software development framework for PHP
 *
 * @package Quantum
 * @author Arman Ag. <arman.ag@softberg.org>
 * @copyright Copyright (c) 2018 Softberg LLC (https://softberg.org)
 * @link http://quantum.softberg.org/
 * @since 2.9.0
 */

namespace Quantum\Libraries\Database\Sleekdb\Statements;

use SleekDB\Exceptions\InvalidConfigurationException;
use Quantum\Libraries\Database\PaginatorInterface;
use Quantum\Libraries\Database\Sleekdb\Paginator;
use SleekDB\Exceptions\InvalidArgumentException;
use Quantum\Libraries\Database\DbalInterface;
use Quantum\Exceptions\DatabaseException;
use Quantum\Exceptions\ModelException;
use SleekDB\Exceptions\IOException;

/**
 * Trait Result
 * @package Quantum\Libraries\Database\Sleekdb\Statements
 */
trait Result
{
    /**
     * @inheritDoc
     * @return array
     * @throws DatabaseException
     * @throws ModelException
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws InvalidConfigurationException
     */
    public function get(): array
    {
        return array_map(function ($element) {
            $item = clone $this;
            $item->data = $element;
            $item->modifiedFields = $element;
            $item->isNew = false;
            return $item;
        }, $this->getBuilder()->getQuery()->fetch());
    }

    /**
     * @inheritDoc
     * @return PaginatorInterface
     * @throws DatabaseException
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws InvalidConfigurationException
     * @throws ModelException
     */
    public function paginate(int $perPage, int $currentPage = 1): PaginatorInterface
    {
        return new Paginator($this, $perPage, $currentPage);
    }

    /**
     * @inheritDoc
     * @return DbalInterface
     * @throws DatabaseException
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws InvalidConfigurationException
     */
    public function findOne(int $id): DbalInterface
    {
        $result = $this->getOrmModel()->findById($id);

        $this->updateOrmModel($result);

        return $this;
    }

    /**
     * @inheritDoc
     * @throws DatabaseException
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws InvalidConfigurationException
     */
    public function findOneBy(string $column, $value): DbalInterface
    {
        $result = $this->getOrmModel()->findOneBy([$column, '=', $value]);

        $this->updateOrmModel($result);

        return $this;
    }

    /**
     * @inheritDoc
     * @throws DatabaseException
     * @throws ModelException
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws InvalidConfigurationException
     */
    public function first(): DbalInterface
    {
        $result = $this->getBuilder()->getQuery()->first();

        $this->updateOrmModel($result);

        return $this;
    }

    /**
     * @inheritDoc
     * @throws DatabaseException
     * @throws ModelException
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws InvalidConfigurationException
     */
    public function count(): int
    {
        return count($this->getBuilder()->getQuery()->fetch());
    }

    /**
     * @inheritDoc
     */
    public function asArray(): array
    {
        $result = $this->data ?: [];

        if (count($this->hidden) > 0 && count($result)) {
            $result = $this->setHidden($result);
        }

        return $result;
    }

    /**
     * @param $result
     * @return array
     */
    public function setHidden($result): array
    {
        return array_diff_key($result, array_flip($this->hidden));
    }
}
