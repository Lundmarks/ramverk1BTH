<?php

namespace Anax\Controller;

use Anax\DI\DIFactoryConfig;
use PHPUnit\Framework\TestCase;

/**
 * Testclass.
 */
class IpValidControllerTest extends TestCase
{
    // Create the di container.
    protected $di;



    /**
     * Prepare before each test.
     */
    protected function setUp()
    {
        global $di;

        // Setup di
        $di = new DIFactoryConfig();
        $di->loadServices(ANAX_INSTALL_PATH . "/config/di");

        // Use a different cache dir for unit test
        $di->get("cache")->setPath(ANAX_INSTALL_PATH . "/test/cache/anax");

        $this->di = $di;
    }


    /* =GET============================================================================================== */


    /**
     * Test the route "indexActionGet".
     */
    public function testIndexActionGet()
    {
        // Setup the controller
        $controller = new IpValidController();
        $controller->setDI($this->di);

        // Test the controller action
        $res = $controller->indexActionGet();
        $body = $res->getBody();
        $this->assertStringContainsString("<h1>Welcome to ipvalid!</h1>", $body);
    }

    /**
     * Test the route "indexActionGet" with IPv4 argument.
     */
    public function testIndexActionGetIPv4()
    {
        // Setup the controller
        $controller = new IpValidController();
        $controller->setDI($this->di);

        // Test the controller action
        $_GET["ip"] = "194.47.150.9";
        $res = $controller->indexActionGet();
        $body = $res->getBody();
        $this->assertStringContainsString("<p style='color:darkseagreen;'>The IP entered is a valid IPv4 address!</p>", $body);
    }

    /**
     * Test the route "indexActionGet" with IPv6 argument.
     */
    public function testIndexActionGetIPv6()
    {
        // Setup the controller
        $controller = new IpValidController();
        $controller->setDI($this->di);

        // Test the controller action
        $_GET["ip"] = "2001:4998:44:3507::8000";
        $res = $controller->indexActionGet();
        $body = $res->getBody();
        $this->assertStringContainsString("<p style='color:darkseagreen;'>The IP entered is a valid IPv6 address!</p>", $body);
    }

    /**
     * Test the route "indexActionGet" with failing argument.
     */
    public function testIndexActionGetFail()
    {
        // Setup the controller
        $controller = new IpValidController();
        $controller->setDI($this->di);

        // Test the controller action
        $_GET["ip"] = "test.ip.not.real";
        $res = $controller->indexActionGet();
        $body = $res->getBody();
        $this->assertStringContainsString("<p style='color:red;'>The IP entered is not a valid IPv4/IPv6 address</p>", $body);
    }


    /* =POST============================================================================================= */


    /**
     * Test the route "indexActionPost".
     */
    public function testIndexActionPost()
    {
        // Setup the controller
        $controller = new IpValidController();
        $controller->setDI($this->di);

        // Test the controller action
        $res = $controller->indexActionPost();
        $this->assertEquals("Body is missing, send IP in body.", $res[0]["result"]);
    }

    /**
    * Test the route "indexActionPost" with IPv4 address.
    */
    public function testIndexActionPostIPv4()
    {
        // Setup the controller
        $controller = new IpValidController();
        $controller->setDI($this->di);

        // Test the controller action
        $_POST["ip"] = "194.47.150.9";
        $res = $controller->indexActionPost();
        $this->assertEquals("IPv4", $res[0]["result"]);
    }

    /**
    * Test the route "indexActionPost" with IPv6 address.
    */
    public function testIndexActionPostIPv6()
    {
        // Setup the controller
        $controller = new IpValidController();
        $controller->setDI($this->di);

        // Test the controller action
        $_POST["ip"] = "2001:4998:44:3507::8000";
        $res = $controller->indexActionPost();
        $this->assertEquals("IPv6", $res[0]["result"]);
    }

    /**
    * Test the route "indexActionPost" with failed inputs.
    */
    public function testIndexActionPostFail()
    {
        // Setup the controller
        $controller = new IpValidController();
        $controller->setDI($this->di);

        // Test the controller action
        $_POST["ip"] = "not.a.real.ip";
        $res = $controller->indexActionPost();
        $this->assertEquals("Invalid IPv4/IPv6 address", $res[0]["result"]);
        unset($_POST["ip"]);

        $_POST["ipx"] = "194.47.150.9";
        $res = $controller->indexActionPost();
        $this->assertEquals("Body is missing, send IP in body.", $res[0]["result"]);

        $_POST["xip"] = "194.47.150.9";
        $res = $controller->indexActionPost();
        $this->assertEquals("Body is missing, send IP in body.", $res[0]["result"]);
    }


    /* =OTHER FUNC======================================================================================= */


    /**
    * Test the route "CatchAll".
    */
    public function testCatchAll()
    {
        // Setup the controller
        $controller = new IpValidController();
        $controller->setDI($this->di);

        // Test the controller action
        $res = $controller->catchAll();
        $body = $res->getBody();
        $this->assertStringContainsString("<h1>Route not found.</h1>", $body);
    }
}
