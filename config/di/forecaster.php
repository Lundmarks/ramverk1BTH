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

                // $di->set("forecaster", "\Anax\Models\Forecaster");


                // $config = $cfg->load("apikeys.php");
                // $geoKey = $config["geoapifyApikey"];
                // $owmKey = $config["openweathermapApikey"];
                // echo "setting apikeys in di.. ";
                // $forecaster->setApiKeys($geoKey, $owmKey); //(geo, owm)
                // echo "..apikeys in di set!";

                return $forecaster;
            }
        ],
    ],
];
