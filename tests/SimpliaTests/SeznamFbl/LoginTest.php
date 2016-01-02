<?php
namespace SimpliaTest\SeznamFbl;

use Simplia\SeznamFbl\Api;
use Simplia\SeznamFbl\IOException;

class LoginTest extends \PHPUnit_Framework_TestCase {
    function testInvalidUsername() {
        $this->setExpectedException(IOException::class);
        $client = new Api('info@example.org', '**********');
        $client->getDomains();
    }
}
