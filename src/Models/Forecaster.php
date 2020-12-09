<?php

namespace Anax\Models;

class Forecaster
{


    protected $owmApiKey = "";
    protected $geoApiKey = "";

    protected $latitude = "";
    protected $longitude = "";

    // protected $temperature = ""; //celsius
    // protected $feelsLike = ""; //also celsius
    // protected $windSpeed = ""; //m/s
    // protected $weatherMain = "";
    // protected $weatherDesc = "";
    protected $todaysWeather = array();

    protected $dailyDate = [];
    protected $dailyDesc = [];
    protected $dailyTemp = [];
    protected $dailyWind = [];
    protected $historicalDate = [];
    protected $historicalDesc = [];
    protected $historicalTemp = [];
    protected $historicalWind = [];

    // ACTUAL USEFUL FUNCS

    public function getWeather($inputLat, $inputLong)
    {
        $this->latitude = $inputLat;
        $this->longitude = $inputLong;
        // $configFile = file_get_contents("../config/config.json");
        // $configFile = json_decode($configFile);
        // $owmApiKey = $configFile->openweathermap->apikey;
        $owmApiKey = $this->owmApiKey;

        // echo "<br>Trying to fetch weather with apikey [r40]: " . $owmApiKey;

        // Fetching epoch times for last 5 days
        $epochDays[0] = strtotime("-5 days");
        $epochDays[1] = strtotime("-4 days");
        $epochDays[2] = strtotime("-3 days");
        $epochDays[3] = strtotime("-2 days");
        $epochDays[4] = strtotime("-1 days");

        $url1 = "https://api.openweathermap.org/data/2.5/onecall?"
        . "lat=" . $inputLat . "&lon=" . $inputLong
        . "&exclude=minutely,hourly,alerts&"
        . "appid=" . $owmApiKey
        . "&units=metric&lang=se";

        //Creating URLs for historical data
        $historicalUrls = array();
        for ($i = 0; $i < 5; $i++) {
            $historicalUrls[$i] = "https://api.openweathermap.org/data/2.5/onecall/timemachine?"
            . "lat=" . $inputLat . "&lon=" . $inputLong
            . "&dt=" . $epochDays[$i]
            . "&appid=" . $owmApiKey
            . "&units=metric&lang=se";
        }

        // Send request
        $urlReq1 = array($url1);
        $urls = array_merge($urlReq1, $historicalUrls);
        $urlCount = count($urls);

        $responseArr = array();
        $master = curl_multi_init();

        for ($i = 0; $i < $urlCount; $i++) {
            $url =$urls[$i];
            $responseArr[$i] = curl_init($url);
            curl_setopt($responseArr[$i], CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($master, $responseArr[$i]);
        }

        do {
            curl_multi_exec($master, $running);
        } while ($running > 0);

        for ($i = 0; $i < $urlCount; $i++) {
            $results[$i] = curl_multi_getcontent($responseArr[$i]);
            $results[$i] = json_decode($results[$i]);
        }

        if (isset($results[0])) {
            // Handle response for request 1

            // Current weather
            $this->todaysWeather["temperature"] = @$results[0]->current->temp;
            $this->todaysWeather["feelsLike"] = @$results[0]->current->feels_like;
            $this->todaysWeather["windSpeed"] = @$results[0]->current->wind_speed;

            $this->todaysWeather["weatherMain"] = @$results[0]->current->weather[0]->main;
            $this->todaysWeather["weatherDesc"] = @$results[0]->current->weather[0]->description;
            // $this->temperature = @$results[0]->current->temp;
            // $this->feelsLike = @$results[0]->current->feels_like;
            // $this->windSpeed = @$results[0]->current->wind_speed;
            //
            // $this->weatherMain = @$results[0]->current->weather[0]->main;
            // $this->weatherDesc = @$results[0]->current->weather[0]->description;

            // Weather next 7 days
            $dailySize = @sizeof($results[0]->daily);
            for ($i=1; $i < @$dailySize; $i++) {
                $this->dailyDate[$i] = $results[0]->daily[$i]->dt;
                $this->dailyDesc[$i] = $results[0]->daily[$i]->weather[0]->description;
                $this->dailyTemp[$i] = $results[0]->daily[$i]->temp->day;
                $this->dailyWind[$i] = $results[0]->daily[$i]->wind_speed;
            }

            // Weather last 5 days
            $resultSize = sizeof($results);
            for ($i=1; $i < $resultSize; $i++) {
                $this->historicalDate[$i] = @$results[$i]->current->dt;
                $this->historicalDesc[$i] = @$results[$i]->current->weather[0]->description;
                $this->historicalTemp[$i] = @$results[$i]->current->temp;
                $this->historicalWind[$i] = @$results[$i]->current->wind_speed;
            }
        }
    }


    public function buildWeatherHtml()
    {
        if (isset($this->todaysWeather["temperature"])) {
            $geoapifyApiKey = $this->geoApiKey;

            // Weather right now
            $lat = $this->latitude;
            $long = $this->longitude;
            $tmpr = $this->todaysWeather["temperature"];
            $flike = $this->todaysWeather["feelsLike"];
            $wSpeed = $this->todaysWeather["windSpeed"];
            $wMain = $this->todaysWeather["weatherMain"];
            $wDesc = $this->todaysWeather["weatherDesc"];

            // Map image link
            $mapLink = "https://maps.geoapify.com/v1/staticmap?style=osm-carto&width=500&height=350&center="
            . "lonlat:" . $long . "," . $lat . "&zoom=16&apiKey=" . $geoapifyApiKey;

            // Coming weather next 7 days
            $comingWeatherTable = $this->genForecastTable();

            // Weather past 5 days
            $histWeatherTbl = $this->genHistoricalTable();

            // String to return *HTML
            $returnHtml = "
            <h5>Vädret just nu</h5>
            <p>
            Vädret vid koordinaterna (" . $lat . ", " . $long . ")
            är <br><br><b>" . $wMain . "</b> (<b>" . $wDesc . "</b>)
            </p>
            <br>
            <p>
            Temperaturen är <b>" . $tmpr ."°C</b>, och känns som <b>" . $flike . "°C</b>.
            </p>
            <p>
            Vindar blåser i upp emot <b>" . $wSpeed . "m/s</b>.</p>

            <h5>Karta</h5>
            <img src=" . $mapLink . " alt='Karta'>

            <h5>Vädret kommande vecka</h5>
            " . $comingWeatherTable . "

            <h5>Vädret tidigare 5 dagar</h5>
            " . $histWeatherTbl . "

            ";

            return $returnHtml;
        }
        return false;
    }

    private function genForecastTable()
    {
        // Generates/returns table containing coming weather
        // Returned as string, HTML
        $htmlTable = "<table><thead><tr><th></th>"; //First cell is empty
        $dailyDateSize = sizeof($this->dailyDate);
        for ($i=1; $i < $dailyDateSize; $i++) {
            $epochDate = $this->dailyDate[$i];
            $day = date("D j", $epochDate);
            $htmlTable .= "<th>" . $day . "</th>";
        }
        $htmlTable .= "</tr></thead><tbody><tr><td><b>Beskrivning</b></td>";
        $dailyDescSize = sizeof($this->dailyDesc);
        for ($i=1; $i < $dailyDescSize; $i++) {
            $htmlTable .= "<td>" . $this->dailyDesc[$i] . "</td>";
        }
        $htmlTable .= "</tr><tr><td><b>Temperatur</b></td>";
        $dailyTSize = sizeof($this->dailyTemp);
        for ($i=1; $i < $dailyTSize; $i++) {
            $htmlTable .= "<td>" . $this->dailyTemp[$i] . "°C</td>";
        }
        $htmlTable .= "</tr><tr><td><b>Vindhastighet</b></td>";
        $dailyWSize = sizeof($this->dailyWind);
        for ($i=1; $i < $dailyWSize; $i++) {
            $htmlTable .= "<td>" . $this->dailyWind[$i] . "m/s</td>";
        }
        $htmlTable .= "</tr></tbody></table>";
        return $htmlTable;
    }

    public function genHistoricalTable()
    {
        // Generates/returns table containing historical weather
        // Returned as string, HTML
        $htmlTable = "<table><thead><tr><th></th>"; //First cell is empty
        $histDateSize = sizeof($this->historicalDate);
        for ($i=1; $i < $histDateSize+1; $i++) {
            $epochDate = $this->historicalDate[$i];
            $day = date("D j", $epochDate);
            $htmlTable .= "<th>" . $day . "</th>";
        }
        $htmlTable .= "</tr></thead><tbody><tr><td><b>Beskrivning</b></td>";
        $histDescSize = sizeof($this->historicalDesc);
        for ($i=1; $i < $histDescSize+1; $i++) {
            $htmlTable .= "<td>" . $this->historicalDesc[$i] . "</td>";
        }
        $htmlTable .= "</tr><tr><td><b>Temperatur</b></td>";
        $dailyWSize = sizeof($this->historicalTemp);
        for ($i=1; $i < $dailyWSize+1; $i++) {
            $htmlTable .= "<td>" . $this->historicalTemp[$i] . "°C</td>";
        }
        $htmlTable .= "</tr><tr><td><b>Vindhastighet</b></td>";
        $dailyWSize = sizeof($this->historicalWind);
        for ($i=1; $i < $dailyWSize+1; $i++) {
            $htmlTable .= "<td>" . $this->historicalWind[$i] . "m/s</td>";
        }
        $htmlTable .= "</tr></tbody></table>";
        return $htmlTable;
    }

    // SET&GET FUNCS


    public function setApiKeys($geoInputKey, $owmInputKey)
    {
        $this->geoApiKey = $geoInputKey;
        $this->owmApiKey = $owmInputKey;
    }

    public function getLatLong()
    {
        if ($this->latitude != "" && $this->longitude != "") {
            $returnArray["lat"] = $this->latitude;
            $returnArray["long"] = $this->longitude;
            return $returnArray;
        }
        return false;
    }

    public function getCompleteFetchedData()
    {
        if (isset($this->latitude) &&
            isset($this->todaysWeather["temperature"])) {
            $currentArray = $this->todaysWeather;
            $dailyArray = array();
            $historicalArray = array();

            $dailyDateSize = sizeof($this->dailyDate);
            for ($i=1; $i < $dailyDateSize+1; $i++) {
                $arrayIdx = "forecast_" . $i;
                $dailyArray[$arrayIdx]["date"] = $this->dailyDate[$i];
                $dailyArray[$arrayIdx]["description"] = $this->dailyDesc[$i];
                $dailyArray[$arrayIdx]["temperature"] = $this->dailyTemp[$i];
                $dailyArray[$arrayIdx]["windspeed"] = $this->dailyWind[$i];
            }
            $histDateSize = sizeof($this->historicalDate);
            for ($i=1; $i < $histDateSize+1; $i++) {
                $arrayIdx = "historical_" . $i;
                $historicalArray[$arrayIdx]["date"] = $this->historicalDate[$i];
                $historicalArray[$arrayIdx]["description"] = $this->historicalDesc[$i];
                $historicalArray[$arrayIdx]["temperature"] = $this->historicalTemp[$i];
                $historicalArray[$arrayIdx]["windspeed"] = $this->historicalWind[$i];
            }
            $returnArray[0] = $currentArray;
            $returnArray[1] = $dailyArray;
            $returnArray[2] = $historicalArray;
            return $returnArray;
        }
        return false;
    }
}
