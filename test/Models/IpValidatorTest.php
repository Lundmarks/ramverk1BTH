<?php

// namespace Anax\Controller;
namespace Anax\Models;

use Anax\DI\DIFactoryConfig;
use PHPUnit\Framework\TestCase;

/**
 * Testclass.
 */
class IpValidatorModelTest extends TestCase
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
    * Test the route "validateIPv4".
    */
    public function testValidateIPv4()
    {
        // Setup the model
        $model = new IpValidator();
        // $model->setDI($this->di);

        $testIp = "46.246.122.43"; //Norwegian VPN IP

        // Test the model action
        $resTrue = $model->validateIPv4($testIp);
        $resFalse = $model->validateIPv6($testIp);

        $this->assertEquals(true, $resTrue);
        $this->assertEquals(false, $resFalse);
    }

    /**
    * Test the route "validateIPv6".
    */
    public function testValidateIPv6()
    {
        // Setup the model
        $model = new IpValidator();
        // $model->setDI($this->di);

        $testIp = "2001:4998:44:3507::8001"; //Yahoo IP

        // Test the model action
        $resTrue = $model->validateIPv6($testIp);
        $resFalse = $model->validateIPv4($testIp);

        $this->assertEquals(true, $resTrue);
        $this->assertEquals(false, $resFalse);
    }


    /**
    * Test the route "resolveHostname".
    */
    public function testResolveHostname()
    {
        // Setup the model
        $model = new IpValidator();
        // $model->setDI($this->di);

        $testIp = "2001:4998:44:3507::8001"; //Yahoo IP
        $fakeIp = "1.2.3.4"; //Fake IP

        // Test the model action
        $resFalse1 = $model->resolveHostname();
        $model->validateIPv6($testIp);
        $resHostname = $model->resolveHostname();
        $model->validateIPv4($fakeIp);
        $resFalse2 = $model->resolveHostname();

        $this->assertEquals(false, $resFalse1);
        $this->assertEquals(true, $resHostname);
        $this->assertEquals(false, $resFalse2);
    }


    /**
    * Test the routes "getIp" and "getHostname".
    */
    public function testGetFuncs()
    {
        // Setup the model
        $model = new IpValidator();
        // $model->setDI($this->di);

        $testIp = "2001:4998:44:3507::8001"; //Yahoo IP

        // Test the model action
        $model->validateIPv6($testIp);
        $model->resolveHostname();

        $this->assertEquals("2001:4998:44:3507::8001", $model->getIp());
        $this->assertEquals("media-router-fp74.prod.media.vip.ne1.yahoo.com", $model->getHostname());
    }
}
