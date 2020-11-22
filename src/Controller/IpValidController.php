<?php

namespace Anax\Controller;

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
class IpValidController implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;



    /**
     * @var string $db a sample member variable that gets initialised
     */
    private $db = "not active";


    public function indexActionGet()
    {
        // Deal with the action and return a response.

        if (isset($_GET['ip'])) {
            // Validate IP!
            // Fetch ip from get
            $fetchedIp = $_GET["ip"];
            // Fetch hostname, if possible
            set_error_handler(function () {
                /* Dont print warning */
            });
            $hostname = gethostbyaddr($fetchedIp);
            restore_error_handler();
            // Validate ipv4/ipv6
            if (filter_var($fetchedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                // $fetchedIp is a valid IPv4 address
                $resultText = "<p style='color:darkseagreen;'>The IP entered is a valid IPv4 address!</p>";
            } elseif (filter_var($fetchedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                // $fetchedIp is a valid IPv6 address
                $resultText = "<p style='color:darkseagreen;'>The IP entered is a valid IPv6 address!</p>";
            } else {
                // $fetchedIp is not a valid IPv4 nor IPv6 address
                $resultText = "<p style='color:red;'>The IP entered is not a valid IPv4/IPv6 address</p>
                <p style='color:silver;'>Feel free to try again!</p>";
            }
            if ($hostname && $hostname != $fetchedIp) {
                // Hostname was successfully resolved
                $hostnameText = "<p>
                Hostname was successfully resolved! -> </p><code>" .
                $hostname .
                "</code>";
            } else {
                // Hostname could not be resolved
                $hostnameText = "<p>
                Hostname could not be resolved for IP.
                </p>";
            }

            // Display results
            $page = $this->di->get("page");
            $page->add("anax/v2/article/default", [
                "content" => "<h1>Welcome to ipvalid!</h1>
                <br>
                <p>You entered the ip <code>" . $_GET["ip"] . "</code>.
                <h5>Hostname</h5>" . $hostnameText . "<h5>Result</h5>" . $resultText,
            ]);
            // $page->add("anax/v2/article/default", [
            //     "content" => "<h2>Debug below</h2><p>IP is set! =" . htmlspecialchars($_GET['ip']) . "=</p>",
            // ]);
        } else {
            // IP is not set!!
            $page = $this->di->get("page");
            $redirectUrl = $_SERVER['PHP_SELF'];
            $page->add("anax/v2/article/default", [
                "content" => '<h1>Welcome to ipvalid!</h1>
                <h5>Information</h5>
                <p>On this page you can verify IPv4/IPv6 addresses, and get a response whether the entered IP address is valid or not.
                You also get the hostname of the entered IP address, if applicable.</p>
                <p>This website is also able to handle POST requests containing IP-address-validation. Simply POST to this page (/ipvalid)
                with a body containing a JSON object containing the key "ip" coupled with the IP you want to validate. The server will then respond
                with JSON data containing the result.</p>
                <p>Further down are some example routes you easily can use, if you simply want to test the validation.</p>
                <p>Start by entering an example IP address below!</p>
                <h5>Enter IPv4/IPv6:</h5>
                <form action="' . $redirectUrl . '" method="get" autocomplete="off">
                  <input type="text" name="ip"><br><br>
                  <input type="submit" value="Validate!">
                </form><br>
                <h5>Test routes</h5>
                <p>Below are some example routes for testing my validation service!</p>
                <ul>
                  <li><a href="?ip=194.47.150.9">IPv4 GET / dbwebb.se</a></li>
                  <li><a href="?ip=2001:4998:44:3507::8000">IPv6 GET / yahoo.com</a></li>
                </ul>
                <h5>POST IP-address</h5>
                <p>Below is a form to test the POST request and getting the JSON response instead!</p>
                <form action="' . $redirectUrl . '" method="post" autocomplete="off">
                  <input type="text" name="ip"><br><br>
                  <input type="submit" value="Validate!">
                </form><br>
                ',
            ]);
        }

        return $page->render([
            "title" => "IP-validering",
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
    public function indexActionPost() : array
    {
        // Deal with the action and return a response.
        // try {
        //     $body = $this->di->get("request")->getBodyAsJson();
        // } catch (\Exception $e) {
        //     // Error catched
        // } //Removed for not being able to be tested
        $body = $this->di->get("request");
        if (isset($_POST["ip"])) {
            $body = $_POST;
        } else {
            $body = false;
        }
        // echo "WEWOO";
        // var_dump($body);
        // echo "WEWOO";
        if (!$body && !isset($body["ip"])) {
            // Body empty
            $resultResponse = "Body is missing, send IP in body.";
            $json = [
                "message" => __METHOD__ . ", POST (\$db is {$this->db})",
                "result" => $resultResponse
            ];
        } else {
            // Else, continue
            if (isset($body["ip"])) {
                // Body contains IP
                $inputIp = $body["ip"];
                // Validate IPv4/IPv6
                set_error_handler(function () {
                    /* Dont print warning */
                });
                $hostname = gethostbyaddr($inputIp);
                restore_error_handler();
                if (filter_var($inputIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    // $inputIp is a valid IPv4 address
                    $resultResponse = "IPv4";
                } elseif (filter_var($inputIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    // $inputIp is a valid IPv6 address
                    $resultResponse = "IPv6";
                } else {
                    // $inputIp is not a valid IPv4 nor IPv6 address
                    $resultResponse = "Invalid IPv4/IPv6 address";
                }
                // $resultResponse = validateIp($inputIp);
                if ($hostname && $hostname != $inputIp) {
                    // Hostname was successfully resolved
                    $hostnameResponse = $hostname;
                } else {
                    // Hostname could not be resolved
                    $hostnameResponse = null;
                }
                // Send results
                $json = [
                    "message" => __METHOD__ . ", POST (\$db is {$this->db})",
                    "inputIp" => $inputIp,
                    "result" => $resultResponse,
                    "hostname" => $hostnameResponse
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
