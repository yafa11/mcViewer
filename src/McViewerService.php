<?php

namespace Yafa11\McViewer;

use Memcache;
use Yafa11\McViewer\Exception\FailureToConnect;
use Yafa11\McViewer\Model\CacheServerSettings;
use Yafa11\McViewer\Model\MemcacheKey;

class McViewerService
{
    /** @var array */
    private $config;
    /** @var CacheServerSettings[] */
    private $cacheSettingsCollection = [];
    /** @var Memcache[] */
    private $cacheServerCollection = [];
    /** @var int */
    private $keysSearched = 0;
    /** @var array */
    private $sortOptions = ['name', 'expiration', 'size', 'slab'];
    /** @var string */
    private $sortBy = 'name';

    /**
     * McViewerService constructor.
     * @param string $pathToConfig
     */
    public function __construct($pathToConfig = 'config/config.ini')
    {
        $this->config = parse_ini_file($pathToConfig, true);
    }


    /**
     * @return Model\CacheServerSettings[]
     */
    public function getServerSettings()
    {
        if (!count($this->cacheSettingsCollection)) {
            foreach ($this->config['memcache_servers'] as $name => $ipAndPort) {
                $server = new CacheServerSettings();
                $server->setName($name);
                $ipAndPortArray = explode(':', $ipAndPort);
                $server->setIp($ipAndPortArray[0]);
                $server->setPort($ipAndPortArray[1]);
                $this->cacheSettingsCollection[$name] = $server;
                $this->cacheSettingsCollection[$name]->setStats($this->getServerStatistics($name));
            }
        }

        return $this->cacheSettingsCollection;
    }


    /**
     * @param $serverName
     * @return mixed
     * @throws FailureToConnect
     */
    public function getServerStatistics($serverName)
    {
        $statsArray = $this->getMemcacheServer($serverName)->getextendedstats();

        return array_shift($statsArray);
    }

    public function getKey($key, $serverName)
    {
        $keyObj = new MemcacheKey();
        $keyObj->setName($key);
        $keyObj->setValue($this->getKeyValue($key, $serverName));

        return $keyObj;
    }

    /**
     * @param string $key
     * @param string $serverName
     * @return string
     */
    public function getKeyValue($key, $serverName)
    {
        $keyValue = $this->getMemcacheServer($serverName)->get($key);
        if (is_scalar($keyValue)) {
            return $keyValue;
        }

        return print_r($keyValue, 1);
    }


    /**
     * @param string $key
     * @param string $serverName
     * @throws FailureToConnect
     */
    public function deleteKey($key, $serverName)
    {
        return $this->getMemcacheServer($serverName)->delete($key);
    }

    /**
     * @param string|null $name
     * @param string|null $value
     * @param string $serverName
     * @param int $slabId
     * @return MemcacheKey[]
     */
    public function findKeysByNameAndValue($name = null, $value = null, $serverName, $slabId = 0)
    {
        $this->keysSearched = 0;
        if ((int)$slabId === 0) {
            return $this->searchAllSlabsForKeyValue($serverName, $name, $value);
        }

        return $this->searchSlabForKeyValue($serverName, $slabId, $name, $value);
    }


    /**
     * @return int
     */
    public function getKeysSearchedTotal()
    {
        return $this->keysSearched;
    }

    /**
     * @param string $sortBy
     */
    public function setSortBy($sortBy)
    {
        $sortBy = strtolower($sortBy);
        if (!in_array($sortBy, $this->sortOptions)) {
            $optionList = implode(', ', $this->sortOptions);
            throw new \UnexpectedValueException(
                'Sort by value must be one of (' . $optionList . '). Received "' . $sortBy . '" instead.'
            );
        }
        $this->sortBy = $sortBy;
    }

    /**
     * @param string $serverName
     * @param string $name
     * @param string $content
     * @return array
     */
    private function searchAllSlabsForKeyValue($serverName, $name, $content)
    {
        $keyCollection = [];
        foreach ($this->getSlabsOnServer($serverName) as $slabId => $slab) {
            $keyCollection = array_merge(
                $keyCollection,
                $this->searchSlabForKeyValue($serverName, $slabId, $name, $content)
            );
        }
        ksort($keyCollection);

        return $keyCollection;
    }

    /**
     * @param string $serverName
     * @param int $slabId
     * @param string $name
     * @param string $content
     * @return array
     */
    private function searchSlabForKeyValue($serverName, $slabId, $name, $content)
    {
        $slabContents = $this->getSlabContents($serverName, $slabId);
        $keyCollection = [];
        foreach ($slabContents as $keyName => $keyMetaData) {
            $this->keysSearched++;
            if (empty($name) || ($name && strpos(strtolower($keyName), strtolower($name)) !== false)) {
                if (!empty($content)) {
                    $keyVal = $this->getKeyValue($keyName, $serverName);
                    if (strpos(strtolower($keyVal), strtolower($content)) === false) {
                        continue;
                    }
                }
                $key = new MemcacheKey();
                $key->setName($keyName);
                $key->setExpiration($keyMetaData[1]);
                $key->setSize($keyMetaData[0]);
                $key->setSlabId($slabId);
                $keyCollection[$this->getSortableKeyName($keyName, $key)] = $key;
            }
        }
        ksort($keyCollection);

        return $keyCollection;
    }

    /**
     * @param string $name
     * @param string $key
     * @return string
     */
    private function getSortableKeyName($name, $key)
    {
        $pad = 0;
        $sortBy = 'getName';
        switch ($this->sortBy) {
            case 'expiration':
                $sortBy = 'getExpiration';
                break;
            case 'size':
                $sortBy = 'getSize';
                $pad = 15;
                break;
            case 'slab':
                $sortBy = 'getSlabId';
                $pad = 4;
                break;
        }

        return McViewerHelper::padZeroes($key->$sortBy(), $pad) . '_' . $name;
    }

    /**
     * @param $serverName
     * @param $slabId
     * @return array
     * @throws FailureToConnect
     */
    private function getSlabContents($serverName, $slabId)
    {
        $slabContents = @$this->getMemcacheServer($serverName)->getExtendedStats('cachedump', (int)$slabId);

        return array_shift($slabContents);
    }


    /**
     * @param $serverName
     * @return Memcache
     * @throws FailureToConnect
     */
    private function getMemcacheServer($serverName)
    {
        $memcacheServer = $this->cacheServerCollection[$serverName] ?: null;
        if (!$memcacheServer instanceof Memcache) {
            $settings = $this->getServerSettings();
            if (isset($settings[$serverName])) {
                $memcacheServer = new Memcache();
                $connected = $memcacheServer->connect($settings[$serverName]->getIp(),
                    $settings[$serverName]->getPort());
                if (!$connected) {
                    throw new FailureToConnect('Could not connect to ' . $serverName . ' (' .
                        $settings[$serverName]->getIp() . ':' . $settings[$serverName]->getPort());
                }
                $this->cacheServerCollection[$serverName] = $memcacheServer;
            }
        }

        return $memcacheServer;
    }


    /**
     * @param $serverName
     * @return array
     */
    private function getSlabsOnServer($serverName)
    {
        $slabArray = $this->getMemcacheServer($serverName)->getextendedstats('slabs');

        return array_shift($slabArray);
    }
}
