<?php
/**
 * Load the sample json controller.
 */
return [
    "routes" => [
        [
            "info" => "Location-based Weather Forecast Controller.",
            "mount" => "locweather",
            "handler" => "\Anax\Controller\LocWeatherController",
        ],
    ]
];
