<?php

declare(strict_types=1);

namespace mmvc\models\data;

abstract class BaseTransactionalRecord extends BaseRecord implements Transactional
{
    public function beginTransaction(): bool
    {
        return $this->dbHelper->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->dbHelper->commit();
    }

    public function rollback(): bool
    {
        return $this->dbHelper->rollback();
    }

    public function inTransaction(): bool
    {
        return $this->dbHelper->inTransaction();
    }
}
