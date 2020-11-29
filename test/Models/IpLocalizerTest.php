<?php

// namespace Anax\Controller;
namespace Anax\Models;

use Anax\DI\DIFactoryConfig;
use PHPUnit\Framework\TestCase;

/**
 * Testclass.
 */
class IpLocalizerModelTest extends TestCase
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


    /**
    * Test the route "localizeIp".
    */
    public function testLocalizeIp()
    {
        // Setup the model
        $model = new IpLocalizer();
        // $model->setDI($this->di);

        $testIp = "46.246.122.43"; //Norwegian VPN IP

        // Test the model action
        $res = $model->localizeIp($testIp);
        $this->assertEquals("Norway", $res["country"]);
        $this->assertEquals("Oslo", $res["city"]);
        $this->assertEquals("59.916", round($res["lat"], 3));
        $this->assertEquals("10.736", round($res["long"], 3));
    }


    /**
    * Test the route "getData".
    */
    public function testGetData()
    {
        // Setup the model
        $model = new IpLocalizer();
        // $model->setDI($this->di);

        $testIp = "46.246.122.43"; //Norwegian VPN IP

        // Test the model action
        $model->localizeIp($testIp);
        $res = $model->getIp();
        $this->assertEquals($testIp, $res);
    }


    /**
    * Test the route "getIp".
    */
    public function testGetIp()
    {
        // Setup the model
        $model = new IpLocalizer();
        // $model->setDI($this->di);

        $testIp = "46.246.122.43"; //Norwegian VPN IP

        // Test the model action
        $model->localizeIp($testIp);
        $res = $model->getData();
        $this->assertEquals("Norway", $res->country_name);
    }
}
