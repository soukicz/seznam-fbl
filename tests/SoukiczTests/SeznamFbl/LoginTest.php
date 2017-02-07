<?php
namespace SoukiczTest\SeznamFbl;

use PHPUnit\Framework\TestCase;
use Soukicz\SeznamFbl\Api;
use Soukicz\SeznamFbl\IOException;

class LoginTest extends TestCase {
    function testInvalidUsername() {
        $this->expectException(IOException::class);
        $client = new Api('info@example.org', '**********');
        $client->getDomains();
    }
}
