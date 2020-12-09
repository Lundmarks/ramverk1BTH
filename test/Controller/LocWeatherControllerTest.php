<?php

namespace Anax\Controller;

use Anax\DI\DIFactoryConfig;
use PHPUnit\Framework\TestCase;

/**
 * Testclass.
 */
class LocWeatherControllerTest extends TestCase
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
     * Test the route "indexActionGet" with no IP, index page.
     */
    public function testIndexActionGet()
    {
        // Setup the controller
        $controller = new LocWeatherController();
        $controller->setDI($this->di);

        unset($_POST["ip"]);
        unset($_GET["ip"]);

        // Test the controller action
        $res = $controller->indexActionGet();
        $body = $res->getBody();
        $this->assertStringContainsString("<p>Skriv in koordinater nedan för att ta fram en väderleksrapport!</p>", $body);
    }

    /**
     * Test the route "indexActionGet" with IP.
     */
    public function testIndexActionGetIp()
    {
        // Setup the controller
        $controller = new LocWeatherController();
        $controller->setDI($this->di);

        unset($_POST["ip"]);
        $_GET["ip"] = "74.6.231.20"; //Yahoo IP

        // Test the controller action
        $res = $controller->indexActionGet();
        $body = $res->getBody();
        // Asssert Lat/long is fetched correctly (at time of writing this)
        $this->assertStringContainsString("Vädret vid koordinaterna (40.731391906738, -73.988403320312)", $body);
    }

    /**
     * Test the route "indexActionGet" with lat/long.
     */
    public function testIndexActionGetLatLong()
    {
        // Setup the controller
        $controller = new LocWeatherController();
        $controller->setDI($this->di);

        unset($_POST["ip"]);
        unset($_GET["ip"]);
        $_GET["lat"] = 33.4;
        $_GET["long"] = 55.2;

        // Test the controller action
        $res = $controller->indexActionGet();
        $body = $res->getBody();
        $this->assertStringContainsString("Vädret vid koordinaterna (33.4, 55.2)", $body);
    }

    /**
     * Test the route "indexActionGet" with failing argument.
     */
    public function testIndexActionGetFail()
    {
        // Setup the controller
        $controller = new LocWeatherController();
        $controller->setDI($this->di);

        // Test the controller action
        $_GET["ip"] = "test.ip.not.real";
        $res = $controller->indexActionGet();
        $body = $res->getBody();
        $this->assertStringContainsString("<h5 style='color:red;'>Vädret misslyckades att hämtas.</h5>", $body);
    }


    /* =POST============================================================================================= */


    /**
     * Test the route "indexActionPost" with an IP address.
     */
    public function testIndexActionPostIp()
    {
        // Setup the controller
        $controller = new LocWeatherController();
        $controller->setDI($this->di);

        unset($_GET["ip"]);

        $_POST["ip"] = "74.6.231.20";

        // Test the controller action
        $res = $controller->indexActionPost();
        $this->assertEquals("40.73139190673828", $res[0]["location"]["lat"]);
    }
    /**
     * Test the route "indexActionPost" with an IP address.
     */
    public function testIndexActionPostLatLong()
    {
        // Setup the controller
        $controller = new LocWeatherController();
        $controller->setDI($this->di);

        unset($_GET["ip"]);
        unset($_POST["ip"]);

        $_POST["lat"] = "15";
        $_POST["long"] = "42";

        // Test the controller action
        $res = $controller->indexActionPost();
        $this->assertEquals("15", $res[0]["location"]["lat"]);
        $this->assertEquals("42", $res[0]["location"]["long"]);
    }

    /**
    * Test the route "indexActionPost" with failed inputs.
    */
    public function testIndexActionPostFail()
    {
        // Setup the controller
        $controller = new LocWeatherController();
        $controller->setDI($this->di);

        unset($_POST["ip"]);
        unset($_POST["lat"]);
        unset($_POST["long"]);
        unset($_GET["ip"]);

        // Test the controller action
        $_POST["ip"] = "not.a.real.ip";
        $res = $controller->indexActionPost();
        $this->assertEquals("Failed to fetch weather/location from entered information. Make sure entered data is correct.", $res[0]["result"]);
        unset($_POST["ip"]);

        $_POST["ipx"] = "194.47.150.9";
        $res = $controller->indexActionPost();
        $this->assertEquals("Body is missing, or fetch failed for some other reason. Make sure you send IP (or lat&long) in body.", $res[0]["result"]);
    }


    /* =OTHER FUNC======================================================================================= */


    /**
    * Test the route "CatchAll".
    */
    public function testCatchAll()
    {
        // Setup the controller
        $controller = new LocWeatherController();
        $controller->setDI($this->di);

        // Test the controller action
        $res = $controller->catchAll();
        $body = $res->getBody();
        $this->assertStringContainsString("<h1>Route not found.</h1>", $body);
    }
}
