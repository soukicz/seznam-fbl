<?php
namespace SoukiczTest\SeznamFbl;

use Soukicz\SeznamFbl\Api;
use Soukicz\SeznamFbl\IOException;

class LoginTest extends \PHPUnit_Framework_TestCase {
    function testInvalidUsername() {
        $this->setExpectedException(IOException::class);
        $client = new Api('info@example.org', '**********');
        $client->getDomains();
    }
}
