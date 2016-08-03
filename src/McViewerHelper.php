<?php

namespace Yafa11\McViewer;

class McViewerHelper
{
    /**
     * Pads a string with '0's
     * @param $string
     * @param int $count
     * @return string
     */
    public static function padZeroes($string, $count = 4)
    {
        $zerosToPad = $count - strlen($string);
        if ($zerosToPad > 0) {
            $zeros = '';
            for ($i = 0; $i < $zerosToPad; $i++) {
                $zeros .= '0';
            }
            $string = $zeros . $string;
        }

        return $string;
    }

    /**
     * Converts data size to human readable format
     * @param int $bytes
     * @return string
     */
    public static function getHumanReadableSize($bytes)
    {
        $sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB');
        foreach ($sizes as $currentSize) {
            if ($bytes < 1000) {
                break;
            } else {
                $bytes = $bytes / 1024;
            }
        }

        return round($bytes, 2) . ' ' . $currentSize;
    }
}
