<?php

namespace Yafa11\McViewer\Model;

use Yafa11\McViewer\McViewerHelper;

class MemcacheKey implements canConvertToArray
{
    /** @var  string */
    protected $name;
    /** @var  int */
    protected $size;
    /** @var  int */
    protected $expiration;
    /** @var  string */
    protected $value;
    /** @var  int */
    protected $slabId;


    /**
     * @param string|null $format
     * @return bool|int|string
     */
    public function getExpiration($format = null)
    {
        if (!empty($format)) {
            return date($format, $this->expiration);
        }

        return $this->expiration;
    }

    /**
     * @param int $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

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
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getHumanReadableSize()
    {
        return McViewerHelper::getHumanReadableSize($this->size);
    }


    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSlabId()
    {
        return $this->slabId;
    }

    /**
     * @param int $slabId
     */
    public function setSlabId($slabId)
    {
        $this->slabId = $slabId;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $keyArray = [];
        $keyArray['name'] = $this->getName();
        $keyArray['size'] = $this->getHumanReadableSize();
        $keyArray['expiration'] = $this->getExpiration('Y-m-d H:i:s');
        $keyArray['value'] = $this->getValue();
        $keyArray['slab_id'] = $this->getSlabId();

        return $keyArray;
    }
}
