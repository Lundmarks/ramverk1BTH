<?php

namespace Anax\Controller;

use Anax\Models\IpLocalizer;
use Anax\Models\IpValidator;
use Anax\Models\Forecaster;
use Anax\Commons\ContainerInjectableInterface;
use Anax\Commons\ContainerInjectableTrait;

// use Anax\Route\Exception\ForbiddenException;
// use Anax\Route\Exception\NotFoundException;
// use Anax\Route\Exception\InternalErrorException;

/**
 * A sample JSON controller to show how a controller class can be implemented.
 * The controller will be injected with $di if implementing the interface
 * ContainerInjectableInterface, like this sample class does.
 * The controller is mounted on a particular route and can then handle all
 * requests for that mount point.
 */
class LocWeatherController implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;



    /**
     * @var string $db a sample member variable that gets initialised
     */
    private $db = "not active";



    public function indexActionGet()
    {
        if ((isset($_GET["lat"]) && isset($_GET["long"])) || isset($_GET["ip"])) {
            // Form has been filled out
            return $this->showResultsPage();
        } else {
            // Form has NOT been filled out
            return $this->showIndexPage();
        }
    }

    public function showIndexPage()
    {
        // Show forms and info
        $redirectUrl = $_SERVER['REQUEST_URI'];

        $page = $this->di->get("page");
        $page->add("anax/v2/article/default", [
           "content" => "<h1>Välkommen till Väderleksrapporter</h1>

           <h5>Väder genom Koordinater</h5>
           <p>Skriv in koordinater nedan för att ta fram en väderleksrapport!</p>
           <form action='" . $redirectUrl . "' method='get' autocomplete='off'>
           <input type='text' name='lat' placeholder='Latitude'>
           <input type='text' name='long' placeholder='Longitude'>
           <br><br>
           <input type='submit' value='Kolla väder'>
           <br><br>

           <h5>Väder genom IP</h5>
           <p>Eller, använd en IP-address istället!</p>
           <form action='" . $redirectUrl . "' method='get' autocomplete='off'>
           <input type='text' name='ip' placeholder='IP-address'>
           <br><br>
           <input type='submit' value='Kolla väder'>
           </form>

           <h5>REST-server</h5>
           <p>Denna sida driver även en REST-server som går att skicka till och få svar ifrån.</p>
           <p>Den används genom att du skickar en POST-request till denna sida (/locweather)
           tillsammans med den typen av lokalisering du vill använda för att få fram vädret. Antingen genom att du fyller body med
           'ip' samt en IP-address, eller så fyller du istället body med två värden: 'lat' och 'long' med latitud
           respektive longitudinella koordinater.</p>
           <p>Svaret du får är i formen JSON, och innehåller dels de koordinater som framtagits/skrivits in (location/lat, location/long),
           dels vädret just nu (under weather/temperature) och även kommande respektive tidigare väder för platsen. De två sistnämnda
           återfinns under 'weather/forecast_#' samt 'weather/historical_#' innehållandes väder, datum och en kort beskrivning.</p>

           <br><br>",
        ]);
        return $page->render([
           "title" => "Väderleksrapporter",
        ]);
    }

    public function showResultsPage()
    {
        // Show forms and info
        // $forecaster = new Forecaster;

        // $di = new \Anax\DI\DI();
        $this->di->set("forecaster", "\Anax\Models\Forecaster");

        $forecaster = $this->di->get("forecaster");
        $config = $this->di->get("configuration");
        $keyConfig = $config->load("apikeys.php");
        $keyGeo = $keyConfig["config"]["geoapifyApikey"];
        $keyOwm = $keyConfig["config"]["openweathermapApikey"];
        $forecaster->setApiKeys($keyGeo, $keyOwm);

        $_GET = array_filter($_GET);

        if (isset($_GET["ip"])) {
            $ipLocalizer = new IpLocalizer;

            $ipLocalizer->localizeIp($_GET["ip"]);
            $fetchedLatLong = $ipLocalizer->getLatLong();

            $fetchedLat = $fetchedLatLong[0];
            $fetchedLong = $fetchedLatLong[1];
        } elseif (isset($_GET["lat"]) && isset($_GET["long"])) {
            $fetchedLat = $_GET["lat"];
            $fetchedLong = $_GET["long"];
        }

        $forecaster->getWeather($fetchedLat, $fetchedLong);

        $weatherHtml = $forecaster->buildWeatherHtml();
        if ($weatherHtml) {
            $reportHtml = $weatherHtml;
        } else {
            $reportHtml = "<h5 style='color:red;'>Vädret misslyckades att hämtas.</h5>
            <p>Testa gärna igen, med en annan IP address/koordinater.</p>";
        }

        $page = $this->di->get("page");
        $page->add("anax/v2/article/default", [
           "content" => "<h1>Välkommen till Väderleksrapporter</h1>

           " . $reportHtml . "

           <br><br>",
        ]);
        return $page->render([
           "title" => "Väderleksrapporter",
        ]);
    }

    /**
     * This is the index method action, it handles:
     * POST METHOD mountpoint
     * POST METHOD mountpoint/
     * POST METHOD mountpoint/index
     *
     * @return array
     */
    public function indexActionPost()
    {
        // Deal with the action and return a response.
        // $body = $this->di->get("request");
        // var_dump($_POST);
        // var_dump($body);

        if (isset($_POST["ip"])) {
            $body = $_POST;
        } elseif ((isset($_POST["lat"]) && isset($_POST["long"]))) {
            $body = $_POST;
        } else {
            $body = false;
        }
        $continue = false;
        if (isset($body["ip"])) {
            // IP is set

            // Verify IP
            $ipLocalizer = new IpLocalizer;
            $ipLocalizer->localizeIp($body["ip"]);
            // Acquire latlong
            $fetchedLatLong = $ipLocalizer->getLatLong();
            $fetchedLat = $fetchedLatLong[0];
            $fetchedLong = $fetchedLatLong[1];
            $continue = true;
        }
        if (isset($body["lat"]) && isset($body["long"])) {
            // Lat and long is set

            $fetchedLat = $body["lat"];
            $fetchedLong = $body["long"];
            $continue = true;
        }
        if ($continue) {
            // Acquire weather information
            $this->di->set("forecaster", "\Anax\Models\Forecaster");

            $forecaster = $this->di->get("forecaster");
            $config = $this->di->get("configuration");
            $keyConfig = $config->load("apikeys.php");
            $keyGeo = $keyConfig["config"]["geoapifyApikey"];
            $keyOwm = $keyConfig["config"]["openweathermapApikey"];
            $forecaster->setApiKeys($keyGeo, $keyOwm);

            $forecaster->getWeather($fetchedLat, $fetchedLong);

            $locationArray = array("lat"=>$fetchedLat, "long"=>$fetchedLong);

            $weatherArray = $forecaster->getCompleteFetchedData();
            if ($weatherArray) {
                $json = [
                    "location" => $locationArray,
                    "weather" => $weatherArray
                ];
            } else {
                $resultResponse = "Failed to fetch weather/location from entered information. Make sure entered data is correct.";
                $json = [
                    "message" => __METHOD__ . ", POST (\$db is {$this->db})",
                    "result" => $resultResponse
                ];
            }
            // Return weather info
            return [$json];
        }

        // If all else fails
        $resultResponse = "Body is missing, or fetch failed for some other reason. Make sure you send IP (or lat&long) in body.";
        $json = [
            "message" => __METHOD__ . ", POST (\$db is {$this->db})",
            "result" => $resultResponse
        ];
        return [$json];
    }


    /**
     * Adding an optional catchAll() method will catch all actions sent to the
     * router. You can then reply with an actual response or return void to
     * allow for the router to move on to next handler.
     * A catchAll() handles the following, if a specific action method is not
     * created:
     * ANY METHOD mountpoint/**
     *
     * @param array $args as a variadic parameter.
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function catchAll(...$args)
    {
        $page = $this->di->get("page");
        $page->add("anax/v2/article/default", [
            "content" => '<h1>Route not found.</h1>
            <p>This route was not found. Contact webmaster if you think this is an error.</p>',
        ]);
        return $page->render([
            "title" => "Route not found",
        ]);
    }
}
