<?php

namespace Anax\Models;

class IpLocalizer
{

    private $accessKey = "da7143733559c00bae204d5f0f1a013a";

    protected $ipAddress = false;
    protected $ipData = false;

    public function localizeIp($inputIp)
    {
        $this->ipAddress = $inputIp;

        $url = "http://api.ipstack.com/" . $inputIp . "?access_key=" . $this->accessKey;
        $data = file_get_contents($url);
        $data = json_decode($data);

        $this->ipData = $data;

        $returnArray = [];
        $returnArray["country"] = $this->ipData->country_name;
        $returnArray["city"] = $this->ipData->city;
        $returnArray["lat"] = $this->ipData->latitude;
        $returnArray["long"] = $this->ipData->longitude;
        $returnArray["emoji"] = $this->ipData->location->country_flag_emoji;

        return $returnArray;
    }

    public function getData()
    {
        return $this->ipData;
    }

    public function getIp()
    {
        return $this->ipAddress;
    }

    public function getLatLong()
    {
        return array($this->ipData->latitude, $this->ipData->longitude);
    }
}
