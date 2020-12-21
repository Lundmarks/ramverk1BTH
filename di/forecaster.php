<?php
/**
 * Configuration file for DI container.
 */
return [
    "services" => [
        "url" => [
            "shared" => true,
            "callback" => function () {
                $forecaster = new \Anax\Models\Forecaster();

                $cfg = $di->get("configuration");

                return $forecaster;
            }
        ],
    ],
];
