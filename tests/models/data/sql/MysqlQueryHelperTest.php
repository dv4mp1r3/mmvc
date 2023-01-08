<?php

declare(strict_types=1);

namespace models\data\sql;

use mmvc\models\data\sql\MysqlQueryHelper;
use PHPUnit\Framework\TestCase;

class MysqlQueryHelperTest extends TestCase
{
    private MysqlQueryHelper $helper;
    public function setUp(): void
    {
        $this->helper = new MysqlQueryHelper();
    }

    public function testBuildDelete(): void {
        $query = $this->helper->buildDelete("test", "1=:val", ['val' => 1]);
        $this->assertEquals("DELETE FROM test  WHERE 1=:val", $query);
        $this->assertEquals([':val' => 1], $this->helper->getQueryValues());
    }
}