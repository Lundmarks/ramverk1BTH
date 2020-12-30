<?php

// namespace Anax\Controller;
namespace Anax\Models;

use Anax\DI\DIFactoryConfig;
use PHPUnit\Framework\TestCase;

/**
 * Testclass.
 */
class ForecasterModelTest extends TestCase
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
        // Load Forecaster (and other services) into DI
        $di->loadServices(ANAX_INSTALL_PATH . "/config/di");

        $this->di = $di;


        $this->di->set("forecaster", "\Anax\Models\Forecaster");
    }


    /**
    * Test the route "getWeather".
    */
    public function testGetWeather()
    {
        // Silicon Valley, approx.
        $testLat = "37.38";
        $testLong = "-122.05";

        $model = $this->di->get("forecaster");

        // Setting API keys manually, since cache is not working in module.
        // Replace next three lines with the two commented to reenable $di-config.
        $keyConfig = array();
        $keyConfig["config"]["geoapifyApikey"] = "d3b6bb0d9aba48baa5cc68cac5c6ac6a";
        $keyConfig["config"]["openweathermapApikey"] = "acf1a83dfb5dd2db934f4d12d3e1b97a";
        // $config = $this->di->get("configuration");
        // $keyConfig = $config->load("apikeys.php");
        $keyGeo = $keyConfig["config"]["geoapifyApikey"];
        $keyOwm = $keyConfig["config"]["openweathermapApikey"];

        $model->setApiKeys($keyGeo, $keyOwm);

        $model->getWeather($testLat, $testLong);

        // Check if fetched weather has temperature
        $objWeatherData = $model->getCompleteFetchedData();
        $objHasTemperature = isset($objWeatherData[0]["temperature"]);
        $this->assertEquals(true, $objHasTemperature);
        $this->assertNotEquals("", $objWeatherData[0]["temperature"]);
    }

    /**
    * Test the route "getLatLong".
    */
    public function testGetLatLong()
    {
        // Silicon Valley, approx.
        $testLat = "37.38";
        $testLong = "-122.05";

        $model = $this->di->get("forecaster");

        // Setting API keys manually, since cache is not working in module.
        // Replace next three lines with the two commented to reenable $di-config.
        $keyConfig = array();
        $keyConfig["config"]["geoapifyApikey"] = "d3b6bb0d9aba48baa5cc68cac5c6ac6a";
        $keyConfig["config"]["openweathermapApikey"] = "acf1a83dfb5dd2db934f4d12d3e1b97a";
        // $config = $this->di->get("configuration");
        // $keyConfig = $config->load("apikeys.php");
        $keyGeo = $keyConfig["config"]["geoapifyApikey"];
        $keyOwm = $keyConfig["config"]["openweathermapApikey"];

        $model->setApiKeys($keyGeo, $keyOwm);

        // Latlong is empty.
        $getResult = $model->getLatLong();
        $this->assertEquals(false, $getResult);

        // Fetch weather (to set private lat/long)
        $model->getWeather($testLat, $testLong);

        $getResult = $model->getLatLong();
        $privateLat = $getResult["lat"];
        $privateLong = $getResult["long"];

        // Make sure latlong is saved in obj
        $this->assertEquals($testLat, $privateLat);
        $this->assertEquals($testLong, $privateLong);
    }

    /**
    * Test the route "buildWeatherHtml".
    */
    public function testBuildHtml()
    {
        $testLat = "12";
        $testLong = "34";

        // Creating model
        $model = $this->di->get("forecaster");

        // Fetch data when weather is not fetched.
        // Resulting in false-response
        $getResult = $model->buildWeatherHtml();
        $this->assertEquals(false, $getResult);

        // Continue setting APIkeys

        // Setting API keys manually, since cache is not working in module.
        // Replace next three lines with the two commented to reenable $di-config.
        $keyConfig = array();
        $keyConfig["config"]["geoapifyApikey"] = "d3b6bb0d9aba48baa5cc68cac5c6ac6a";
        $keyConfig["config"]["openweathermapApikey"] = "acf1a83dfb5dd2db934f4d12d3e1b97a";
        // $config = $this->di->get("configuration");
        // $keyConfig = $config->load("apikeys.php");
        $keyGeo = $keyConfig["config"]["geoapifyApikey"];
        $keyOwm = $keyConfig["config"]["openweathermapApikey"];

        $model->setApiKeys($keyGeo, $keyOwm);

        // Fetch weather
        $model->getWeather($testLat, $testLong);

        // Fetch generated HTML
        $generatedHtml = $model->buildWeatherHtml();

        // Check if page contains lat
        $privateLat = $model->getLatLong()["lat"];
        $expectedString = "Vädret vid koordinaterna (" . $privateLat;
        $this->assertStringContainsString($expectedString, $generatedHtml);
    }

    /**
    * Test the route "getCompleteFetchedData" when no data is fetched.
    */
    public function testFetchEmptyData()
    {
        $model = $this->di->get("forecaster");

        // Fetch data when weather is not fetched.
        $getResult = $model->getCompleteFetchedData();
        $this->assertEquals(false, $getResult);
    }
}
