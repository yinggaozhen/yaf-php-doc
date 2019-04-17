<?php

namespace tests\configs;

use PHPUnit\Framework\TestCase;
use Yaf\Config\Ini;

class IniTest extends TestCase
{
    private const INI_APPLICATION = TEST_DIR . '/common/configs/application.ini';

    /**
     * @throws \Exception
     */
    public function testIniParse()
    {
        $ini = new Ini(self::INI_APPLICATION);
        $ini->get();
    }
}
