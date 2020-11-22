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
class SampleJsonController implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;



    /**
     * @var string $db a sample member variable that gets initialised
     */
    private $db = "not active";



    /**
     * The initialize method is optional and will always be called before the
     * target method/action. This is a convienient method where you could
     * setup internal properties that are commonly used by several methods.
     *
     * @return void
     */
    public function initialize() : void
    {
        // Use to initialise member variables.
        $this->db = "active";
    }



    /**
     * This is the index method action, it handles:
     * GET METHOD mountpoint
     * GET METHOD mountpoint/
     * GET METHOD mountpoint/index
     *
     * @return array
     */
    public function indexActionGet() : array
    {
        // Deal with the action and return a response.
        $json = [
            "message" => __METHOD__ . ", GET (\$db is {$this->db})",
        ];
        return [$json];
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
        //     $body = "Body is missing, send IP in body.";
        // }
        $body = $this->di->get("request")->getBodyAsJson();
        if ($body == "" || $body == null) {
            # Body empty
            $body = "Body is missing, send IP in body.";
        } else {
            # Else, continue
        }


        $json = [
            "message" => __METHOD__ . ", POST (\$db is {$this->db})",
            "body" => $body
        ];
        return [$json];
    }



    /**
     * This sample method dumps the content of $di.
     * GET mountpoint/dump-app
     *
     * @return array
     */
    public function dumpDiActionGet() : array
    {
        // Deal with the action and return a response.
        $services = implode(", ", $this->di->getServices());
        $json = [
            "message" => __METHOD__ . "<p>\$di contains: $services",
            "di" => $this->di->getServices(),
        ];
        return [$json];
    }



    /**
     * Try to access a forbidden resource.
     * ANY mountpoint/forbidden
     *
     * @return array
     */
    public function forbiddenAction() : array
    {
        // Deal with the action and return a response.
        $json = [
            "message" => __METHOD__ . ", forbidden to access.",
        ];
        return [$json, 403];
    }

    /**
     * This is how a general helper method can be created in the controller.
     *
     * @param string $method as the method that handled the controller
     *                       action.
     * @param array  $args   as an array of arguments.
     *
     * @return string as a message to output to help understand how the
     *                controller method works.
     */
    private function getDetailsOnRequest(
        string $method,
        array $args = []
    ) : string {
        $request     = $this->di->get("request");
        $router      = $this->di->get("router");
        $path        = $request->getRoute();
        $httpMethod  = $request->getMethod();
        $mount       = rtrim($router->getLastRoute(), "/");
        $numArgs     = count($args);
        $strArgs     = implode(", ", $args);
        $queryString = http_build_query($request->getGet(), '', ', ');

        return <<<EOD
            <h1>$method</h1>

            <p>The controller mountpoint is '$mount'.
            <p>The request was '$path' ($httpMethod).
            <p>Got '$numArgs' arguments: '$strArgs'.
            <p>Query string contains: '$queryString'.
            <p>\$db is '{$this->db}'.
        EOD;
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
        $data = [
            "content" => $this->getDetailsOnRequest(__METHOD__, $args),
        ];
        $page->add("anax/v2/article/default", $data);

        return [$data, 400];
    }
}
