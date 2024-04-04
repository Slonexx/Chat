<?php
namespace App\Services;
use Illuminate\Support\Facades\Config;

class MsFilterService{
     
    private array $url;

    function __construct() {
        $this->url = Config::get('Global');
    }

    public function getUrl($urlIdentifier) {
        return $this->url[$urlIdentifier];
    }
    
    public function prepareUrlForFilter(string $masterUrlIdentifier, string $slaveUrlIdentifier, string $attributeId, mixed $filterValue) {
        $primaryURL =  $this->url[$masterUrlIdentifier];

        $joinedSecondaryURL =  $this->url[$slaveUrlIdentifier] . $attributeId;

        return "{$primaryURL}?filter={$joinedSecondaryURL}={$filterValue};";

    }

    public function prepareUrlForNotFilterArray(string $masterUrlIdentifier, string $slaveUrlIdentifier, string $attributeId, mixed $filterValue = "", array $params) {
        $primaryURL =  $this->url[$masterUrlIdentifier];

        $joinedSecondaryURL =  $this->url[$slaveUrlIdentifier] . $attributeId;

        $url = "{$primaryURL}?filter={$joinedSecondaryURL}!={$filterValue};";

        foreach($params as $object){
            foreach($object as $key => $value){
                $url = "{$url}{$key}={$value};";
            }
        }

        return $url;

    }

    public function prepareUrlWithParam(string $masterUrlIdentifier, string $param, mixed $filterValue) {
        $primaryURL =  $this->url[$masterUrlIdentifier];

        return "{$primaryURL}?filter={$param}={$filterValue}";

    }

    public function prepareUrlWithUrl(string $masterUrlIdentifier, string $param, string $valueUrlIdentifier, string $valueId) {
        $primaryURL =  $this->url[$masterUrlIdentifier];

        $valueUrl =  $this->url[$valueUrlIdentifier];

        return "{$primaryURL}?filter={$param}={$valueUrl}{$valueId}";

    }
    /**
     * @param string $masterUrlIdentifier главный url запроса
     * @param mixed $params Принимает параметры:
     * 1) filterKey
     * 2) filterValue
     */
    public function prepareUrlWithParams(string $masterUrlIdentifier, mixed ...$params) : string {
        $primaryURL =  $this->url[$masterUrlIdentifier];
        
        $url = "{$primaryURL}?filter=";

        // foreach($values as $value){
        //     $url += "{$param}={$value}";
        // }
        
        for($i = 0; $i < count($params)/2; $i=$i+2) {
            $url += "{$params[$i]}={$params[$i+1]};";
        }
        return $url;

    }
    /**
     * @param string $masterUrlIdentifier главный url запроса
     * @param array $params Принимает:
     * [
     *  stdClass {$key1 => $value1},
     *  stdClass {$key2 => $value2},
     *  ...
     * ]
     */
    public function prepareUrlWithArray(string $masterUrlIdentifier, array $params) : string {
        $primaryURL =  $this->url[$masterUrlIdentifier];
        
        $url = "{$primaryURL}?filter=";

        foreach($params as $key => $value){
            $url = "{$url}{$key}={$value};";
        }
        
        return $url;

    }
}