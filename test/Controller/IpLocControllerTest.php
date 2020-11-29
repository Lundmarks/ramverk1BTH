<?php

namespace Anax\Controller;

use Anax\DI\DIFactoryConfig;
use PHPUnit\Framework\TestCase;

/**
 * Testclass.
 */
class IpLocControllerTest extends TestCase
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
        $controller = new IpLocController();
        $controller->setDI($this->di);

        // Wont be needed in production, when $_SERVER is set
        $_SERVER['REQUEST_URI'] = "temp";
        $_SERVER['REMOTE_ADDR'] = "temp";

        // Test the controller action without an IP set
        $res = $controller->indexActionGet(); //Returns the index-page
        $body = $res->getBody();
        $this->assertStringContainsString("<h1>Välkommen till ip-lokaliseringssidan!</h1>", $body);

        // Test the controller action with an IP set
        $_GET["ip"] = "46.246.122.43"; //Norwegian VPN IP
        $res = $controller->indexActionGet(); //Returns the index-page
        $body = $res->getBody();
        $this->assertStringContainsString("<p style='font-weight:bold'>Oslo, Norway", $body);
    }


    /* =POST============================================================================================= */


    /**
     * Test the route "indexActionPost".
     */
    public function testIndexActionPost()
    {
        // Setup the controller
        $controller = new IpLocController();
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
        $controller = new IpLocController();
        $controller->setDI($this->di);

        // Test the controller action
        $_POST["ip"] = "194.47.150.9";
        $res = $controller->indexActionPost();
        $this->assertEquals("194.47.150.9", $res[0]["inputIp"]);
        $this->assertEquals("Karlskrona", $res[0]["city"]);
    }

    /**
    * Test the route "indexActionPost" with IPv6 address.
    */
    public function testIndexActionPostIPv6()
    {
        // Setup the controller
        $controller = new IpLocController();
        $controller->setDI($this->di);

        // Test the controller action
        $_POST["ip"] = "2001:4998:44:3507::8000";
        $res = $controller->indexActionPost();
        $this->assertEquals("2001:4998:44:3507::8000", $res[0]["inputIp"]);
        $this->assertEquals("Manhattan", $res[0]["city"]);
    }

    /**
    * Test the route "indexActionPost" with an invalid IP.
    */
    public function testIndexActionPostInvalidIp()
    {
        // Setup the controller
        $controller = new IpLocController();
        $controller->setDI($this->di);

        // Test the controller action
        $_POST["ip"] = "not.an.ip";
        $res = $controller->indexActionPost();
        $this->assertEquals("IP entered could not be validated. Please try again", $res[0]["result"]);
    }

    /**
    * Test the route "indexActionPost" with an invalid hostname.
    */
    public function testIndexActionPostNoHostname()
    {
        // Setup the controller
        $controller = new IpLocController();
        $controller->setDI($this->di);

        // Test the controller action
        $_POST["ip"] = "46.246.122.43";
        $res = $controller->indexActionPost();
        $this->assertEquals(null, $res[0]["hostname"]);
    }


    /* =OTHER FUNC======================================================================================= */


    /**
    * Test the route "validateIp".
    */
    public function testValidateIp()
    {
        // Setup the controller
        $controller = new IpLocController();
        $controller->setDI($this->di);

        $testIpOne = "1.2.3.4"; //Fake IP, validate but no hostname
        $testIpTwo = "98.137.11.163"; //Yahoo IP, validates with hostname
        $testIpThree = "2001:4998:44:3507::8000"; //Yahoo IPv6, validates with hostname
        $testIpFour = "not.an.ip"; //Not an IP.

        // Test the controller action
        $resOne = $controller->validateIp($testIpOne);
        $resTwo = $controller->validateIp($testIpTwo);
        $resThree = $controller->validateIp($testIpThree);
        $resFour = $controller->validateIp($testIpFour);

        $this->assertEquals("IPv4", $resOne[0]);
        $this->assertEquals(false, $resOne[1]);
        $this->assertEquals("IPv4", $resTwo[0]);
        $this->assertEquals("media-router-fp74.prod.media.vip.gq1.yahoo.com", $resTwo[1]);
        $this->assertEquals("IPv6", $resThree[0]);
        $this->assertEquals(false, $resFour[0]);
    }

    /**
    * Test the route "showIndexPage".
    */
    public function testShowIndexPage()
    {
        // Setup the controller
        $controller = new IpLocController();
        $controller->setDI($this->di);

        $exampleIp = "12.34.56.78";
        $falseIp = "::1";

        // Wont be needed in production, when $_SERVER is set
        $_SERVER['REQUEST_URI'] = "temp";
        $_SERVER['REMOTE_ADDR'] = "temp";
        $res = $controller->showIndexPage();
        $body1 = $res->getBody();

        $_SERVER['HTTP_CLIENT_IP'] = $exampleIp;
        $res = $controller->showIndexPage();
        $body2 = $res->getBody();
        unset($_SERVER['HTTP_CLIENT_IP']);

        $_SERVER['REMOTE_ADDR'] = $falseIp;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $falseIp;
        $res = $controller->showIndexPage();
        $body3 = $res->getBody();

        // Test the controller action
        $this->assertStringContainsString("<h1>Välkommen till ip-lokaliseringssidan!</h1>", $body1);
        $this->assertStringContainsString("<input type='text' name='ip' value='12.34.56.78'>", $body2);
        $this->assertStringContainsString("<input type='text' name='ip' value='temp'>", $body3);
    }


    /**
    * Test the route "CatchAll".
    */
    public function testCatchAll()
    {
        // Setup the controller
        $controller = new IpLocController();
        $controller->setDI($this->di);

        // Test the controller action
        $res = $controller->catchAll();
        $body = $res->getBody();
        $this->assertStringContainsString("<h1>Route not found.</h1>", $body);
    }
}
