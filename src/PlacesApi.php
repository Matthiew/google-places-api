<?php

namespace SKAgarwal\GoogleApi;

use GuzzleHttp\Client;
use SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException;

class PlacesApi
{
    const BASE_URL = 'https://maps.googleapis.com/maps/api/place/';
    
    const NEARBY_SEARCH_URL = 'nearbysearch/json';
    
    const TEXT_SEARCH_URL = 'textsearch/json';
    
    const FIND_PLACE = 'findplacefromtext/json';
    
    const DETAILS_SEARCH_URL = 'details/json';
    
    const PLACE_AUTOCOMPLETE_URL = 'autocomplete/json';
    
    const QUERY_AUTOCOMPLETE_URL = 'queryautocomplete/json';
    
    const PLACE_ADD_URL = 'add/json';
    
    const PLACE_DELETE_URL = 'delete/json';
    
    
    
    /**
     * @var
     */
    public $status;
    
    /**
     * @var null
     */
    private $key = null;
    
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    
    /**
     * @var bool
     */
    private $verifySSL = true;
    
    /**
     * PlacesApi constructor.
     *
     * @param null $key
     * @param bool $verifySSL
     */
    public function __construct($key = null, $verifySSL = true)
    {
        $this->key = $key;

        $this->verifySSL = $verifySSL;
        
        $this->client = new Client([
            'base_url' => self::BASE_URL,
        ]);
    }
    
    /**
     * Find Place Request to google places api.
     *
     * @param string $input (for example, a name, address, or phone number)
     * @param string $inputType (textquery or phonenumber)
     * @param array $params
     *
     * @return array
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    public function findPlace($input, $inputType, $params = [])
    {
        $this->checkKey();
        
        $params['input'] = $input;
        
        $params['inputtype'] = $inputType;
        
        return $this->makeRequest(self::FIND_PLACE, $params);
    }
    
    /**
     * Place Nearby Search Request to google api.
     *
     * @param $location
     * @param null $radius
     * @param array $params
     *
     * @return array
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    public function nearbySearch($location, $radius = null, $params = [])
    {
        $this->checkKey();
        
        $params = $this->prepareNearbySearchParams($location, $radius, $params);
        return $this->makeRequest(self::NEARBY_SEARCH_URL, $params);
    }
    
    /**
     * Place Text Search Request to google places api.
     *
     * @param $query
     * @param array $params
     *
     * @return array
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    public function textSearch($query, $params = [])
    {
        $this->checkKey();
        
        $params['query'] = $query;
        return $this->makeRequest(self::TEXT_SEARCH_URL, $params);
        
    }
    
    /**
     * Place Details Request to google places api.
     *
     * @param $placeId
     * @param array $params
     *
     * @return array
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    public function placeDetails($placeId, $params = [])
    {
        $this->checkKey();
        
        $params['placeid'] = $placeId;
        
        return $this->makeRequest(self::DETAILS_SEARCH_URL, $params);
    }
    
    /**
     * Place AutoComplete Request to google places api.
     *
     * @param $input
     * @param array $params
     *
     * @return array
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    public function placeAutocomplete($input, $params = [])
    {
        $this->checkKey();
        
        $params['input'] = $input;
        
        return $this->makeRequest(self::PLACE_AUTOCOMPLETE_URL, $params);
    }
    
    /**
     * Query AutoComplete Request to the google api.
     *
     * @param $input
     * @param array $params
     *
     * @return array
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    public function queryAutocomplete($input, $params = [])
    {
        $this->checkKey();
        
        $params['input'] = $input;
        
        return $this->makeRequest(self::QUERY_AUTOCOMPLETE_URL, $params);
    }
    
    /**
     * @param $uri
     * @param $params
     * @param $method
     *
     * @return mixed|string
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    private function makeRequest($uri, $params, $method = 'get')
    {
        $options = $this->getOptions($params, $method);
        
        $response = json_decode(
            $this->client->$method($uri, $options)->getBody()->getContents(),
            true
        );
        
        $this->setStatus($response['status']);
        
        if ($response['status'] !== 'OK'
            AND $response['status'] !== 'ZERO_RESULTS') {
            throw new GooglePlacesApiException(
                "Response returned with status: " . $response['status'] . "\n" .
                array_key_exists('error_message', $response)
                    ?: "Error Message: {$response['error_message']}"
            );
        }
        
        return $response;
    }
    
    /**
     * @param mixed $status
     */
    private function setStatus($status)
    {
        $this->status = $status;
    }
    
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * @return null
     */
    public function getKey()
    {
        return $this->key;
    }
    
    /**
     * @param null $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        
        return $this;
    }
    
    /**
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    private function checkKey()
    {
        if (!$this->key) {
            throw new GooglePlacesApiException('API KEY is not specified.');
        }
    }
    
    /**
     * Prepare the params for the Place Search.
     *
     * @param $location
     * @param $radius
     * @param $params
     *
     * @return mixed
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    private function prepareNearbySearchParams($location, $radius, $params)
    {
        $params['location'] = $location;
        $params['radius'] = $radius;
        
        if (array_key_exists('rankby', $params)
            AND $params['rankby'] === 'distance'
        ) {
            unset($params['radius']);
            
            if (!array_any_keys_exists(['keyword', 'name', 'type'], $params)) {
                throw new GooglePlacesApiException("Nearby Search require one"
                    . " or more of 'keyword', 'name', or 'type' params since 'rankby' = 'distance'.");
            }
        } elseif (!$radius) {
            throw new GooglePlacesApiException("'radius' param is not defined.");
        }
        
        return $params;
    }
    
    /**
     * @param bool $verifySSL
     *
     * @return PlacesApi
     */
    public function verifySSL($verifySSL = true)
    {
        $this->verifySSL = $verifySSL;
        
        return $this;
    }
    
    /**
     * @param array $params
     * @param string $method
     *
     * @return array
     */
    private function getOptions($params, $method = 'get')
    {
        $options = [
            'query' => [
                'key' => $this->key,
            ],
        ];
        
        if ($method == 'post') {
            $options = array_merge(['body' => json_encode($params)], $options);
        } else {
            $options['query'] = array_merge($options['query'], $params);
        }
        
        $options['http_errors'] = false;
        
        $options['verify'] = $this->verifySSL;
        
        return $options;
    }
}
