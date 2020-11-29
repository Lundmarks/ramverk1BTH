<?php

namespace Anax\Controller;

use Anax\Models\IpLocalizer;
use Anax\Models\IpValidator;
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
class IpLocController implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;



    /**
     * @var string $db a sample member variable that gets initialised
     */
    private $db = "not active";

    public function validateIp($inputIp)
    {
        $ipValidator = new IpValidator;
        $isValid = false;
        $returnArray = [];
        if ($ipValidator->validateIPv4($inputIp)) {
            $isValid = true;
            array_push($returnArray, "IPv4");
        } elseif ($ipValidator->validateIPv6($inputIp)) {
            $isValid = true;
            array_push($returnArray, "IPv6");
        } else {
            return false;
        }
        if ($isValid) {
            if ($ipValidator->resolveHostname()) {// Try to get hostname if IP is a real IP
                $hostname = $ipValidator->getHostname();// Found hostname
            } else {
                $hostname = false;
            }
            array_push($returnArray, $hostname);
        }
        return $returnArray;
    }


    public function indexActionGet()
    {
        if (!isset($_GET["ip"])) {
            // Show forms and info
            return $this->showIndexPage();
        } else {
            // Show results
            $inputIp = $_GET["ip"];
            $page = $this->di->get("page");
            $ipLocation = new IpLocalizer;
            $resultArr = $ipLocation->localizeIp($inputIp);
            $resultValidation = $this->validateIp($inputIp);
            $city = $resultArr["city"];
            $country = $resultArr["country"];
            $emoji = $resultArr["emoji"];
            $lat = round($resultArr["lat"], 3);
            $long = round($resultArr["long"], 3);
            //Build strings to display
            $ipTypeText = "";
            if (isset($resultValidation[0])) {
                $ipTypeText = "<p>You have entered the IP <b>" . $inputIp . "</b>, which is an <b>" . $resultValidation[0] . "</b> address.</p>";
                if (isset($resultValidation[1])) {
                    $ipTypeText .= "<p>The IP has hostname <b>" . $resultValidation[1] . "</b>.</p>";
                }
            }
            $ipLocationText = "I could not fetch location data for your IP, unfortunately.";
            if (isset($resultArr["country"])) {
                $ipLocationText = "
                <p>IP Location:</p>
                <p style='font-weight:bold'>" . $city . ", " . $country . ". " . $emoji . "<br>
                (" . $lat . ", " . $long . ")</p>";
            }
            $page->add("anax/v2/article/default", [
                "content" => "<h1>Ip-lokaliseringsresultat</h1><br>
                <p>" . $ipTypeText . "</p>
                <p>" . $ipLocationText . "</p><br>
                <h5>Debug/test</h5>
                <p>IP entered: =" . $_GET["ip"] . "=</p>",
            ]);
            return $page->render([
            "title" => "IP-lokalisering",
            ]);
        }
    }

    public function showIndexPage()
    {
        // Show forms and info
        $redirectUrl = $_SERVER['REQUEST_URI'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {// Set user IP, if possible
            $userIp = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $userIp = $_SERVER['REMOTE_ADDR'];
        }
        if ($userIp == "::1") {
            $userIp = "";
        }
        $page = $this->di->get("page");
        $page->add("anax/v2/article/default", [
           "content" => "<h1>Välkommen till ip-lokaliseringssidan!</h1>
           <h5>IP-lokalisering</h5>
           <p>Skriv in en IP nedan för att lokaliserad den!</p>
           <form action='" . $redirectUrl . "' method='get' autocomplete='off'>
           <input type='text' name='ip' value='" . $userIp . "'><br><br>
           <input type='submit' value='Lokalisera'>
           </form>
           <p>(Förifyllt finner du din egna IP, om möjligt)</p>
           <h5>REST-API</h5>
           <p>Denna sida tillhandahåller även ett REST-API som gör det möjligt för
           dig att POSTa till denna sida och få samma resultat men i ett JSON-objekt!</p>
           <p>Skicka helt enkelt en POST-request till denna URL med ett dataobjekt som innehåller paret
           'ip' tillsammans med den IP du vill lokalisera, så kommer du få ett JSON-svar!</p>
           <br>
           <p>Nedan finner du en knapp som leder till ett exempel på hur svaret på REST-APIn ser ut!</p>
           <form method='post' action='" . $redirectUrl . "'>
             <input type='hidden' name='ip' value='194.47.150.9'>
             <button type='submit' name='submit-param' value='submit-value'>
               Test POST-lokalisering
             </button>
           </form>
           <br>",
        ]);
        return $page->render([
           "title" => "IP-lokalisering",
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
        $body = $this->di->get("request");

        if (isset($_POST["ip"])) {
            $body = $_POST;
        } else {
            $body = false;
        }

        if (!$body && !isset($body["ip"])) {
            // Body empty
            $resultResponse = "Body is missing, send IP in body.";
            $json = [
                "message" => __METHOD__ . ", POST (\$db is {$this->db})",
                "result" => $resultResponse
            ];
        } else {
            // Else, continue
            $inputIp = $body["ip"];

            $ipLocation = new IpLocalizer;
            $resultArr = $ipLocation->localizeIp($inputIp);

            $ipValidator = new IpValidator;
            $isValid = false;
            if ($ipValidator->validateIPv4($inputIp)) {
                // IP is IPv4
                $isValid = true;
                $ipType = "IPv4";
            } elseif ($ipValidator->validateIPv6($inputIp)) {
                // IP is IPv6
                $isValid = true;
                $ipType = "IPv6";
            } else {
                $ipType = false;
            }
            if ($isValid) {
                // Try to get hostname if IP is a real IP
                if ($ipValidator->resolveHostname()) {
                    // Found hostname
                    $hostname = $ipValidator->getHostname();
                } else {
                    $hostname = false;
                }
                $city = $resultArr["city"];
                $country = $resultArr["country"];
                $lat = round($resultArr["lat"], 3);
                $long = round($resultArr["long"], 3);

                $json = [
                    "inputIp" => $inputIp,
                    "hostname" => $hostname,
                    "city" => $city,
                    "country" => $country,
                    "latitude" => $lat,
                    "longitude" => $long
                ];
            } else {
                $resultResponse = "IP entered could not be validated. Please try again";
                $json = [
                    "message" => __METHOD__ . ", POST (\$db is {$this->db})",
                    "result" => $resultResponse
                ];
            }
        }
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
