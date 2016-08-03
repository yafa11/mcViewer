<?php

namespace Yafa11\McViewer\Model;

class CacheServerSettings implements canConvertToArray
{
    /** @var  string */
    private $name;
    /** @var  string */
    private $ip;
    /** @var  int */
    private $port;
    /** @var  array */
    private $stats;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param array $stats
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
