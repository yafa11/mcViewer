<?php
// Ugly light weight API for the mcViewer

use Yafa11\McViewer\McViewerService;
use Yafa11\McViewer\Model\canConvertToArray;

require_once __DIR__ . '/../../vendor/autoload.php';

$action = $_GET['a'];
$serverName = $_GET['sn'];
$cacheKey = $_GET['k'];
$sortBy =  $_GET['sb'];
$content = $_GET['c'];
$mcViewerService = new McViewerService();
if(!empty($sortBy)){
    $mcViewerService->setSortBy($sortBy);
}

function convertToArray($collection){
    $newCollection = [];
    foreach($collection as $object){
        if($object instanceof canConvertToArray){
            $newCollection[] = $object->toArray();
        }else{
            $newCollection[] = $object;
        }
    }
    return $newCollection;
}

$body = '';

switch($action){
    case 'getServers':
        $body = convertToArray($mcViewerService->getServerSettings());
        break;
    case 'getKeys':
        $keys = $mcViewerService->findKeysByNameAndValue($cacheKey,$content,$serverName);
        $matchCount = count($keys);
        $totalCount = $mcViewerService->getKeysSearchedTotal();
        $keyArray = convertToArray($keys);
        $resultArray = ['totalCount'=>$totalCount,'matchCount'=>$matchCount,'keys'=>$keyArray];
        $body = $resultArray;
        break;
    case 'getKey':
        $key = $mcViewerService->getKey($cacheKey, $serverName);
        $body = $key->toArray();
        break;
    case 'deleteKey':
        $result = $mcViewerService->deleteKey($cacheKey, $serverName);
        $body = ['success'=>$result];
        break;
}

header('Content-Type: application/json');
echo json_encode($body);