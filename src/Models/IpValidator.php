<?php

namespace Anax\Models;

class IpValidator
{

    protected $ipAddress = "";
    protected $hostname = "";

    public function validateIPv4($inputIp)
    {
        if (filter_var($inputIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // $inputIp is a valid IPv4 address
            $this->ipAddress = $inputIp;
            return true;
        }
        return false;
    }

    public function validateIPv6($inputIp)
    {
        if (filter_var($inputIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // $inputIp is a valid IPv6 address
            $this->ipAddress = $inputIp;
            return true;
        }
        return false;
    }

    public function resolveHostname()
    {
        if ($this->ipAddress == "") {
            return false;
        }
        $inputIp = $this->ipAddress;

        set_error_handler(function () {
            /* Dont print warning */
        });
        $hostname = gethostbyaddr($inputIp);
        restore_error_handler();

        if ($hostname && $hostname != $inputIp) {
            // Hostname was successfully resolved
            $this->hostname = $hostname;
            return true;
        }
        return false;
    }

    public function getIp()
    {
        return $this->ipAddress;
    }
    public function getHostname()
    {
        return $this->hostname;
    }
}
