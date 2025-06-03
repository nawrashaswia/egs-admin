<?php

namespace App\Helpers\Core;

use PDO;
use App\Helpers\Core\TracingDBStatement;

class TracingDBPDO extends PDO
{
    public function __construct($dsn, $username, $password, $options)
    {
        parent::__construct($dsn, $username, $password, $options);

        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [TracingDBStatement::class]);
    }
}
