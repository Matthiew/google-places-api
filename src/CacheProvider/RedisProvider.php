<?php

namespace SKAgarwal\GoogleApi\CacheProvider;

use Predis\ClientInterface;

class RedisProvider implements CacheProviderInterface
{
    protected $client;
    public $msgTTL;

    public function __construct(ClientInterface $client, $msgTTL = 0)
    {
        $this->client = $client;
        $this->msgTTL = $msgTTL;
    }

    /**
     * Set the key and the value to cache and you can add a ttl
     *
     * @param $key
     * @param $value
     * @param $ttl
     * @return mixed
     */
    public function set($key, $value, $ttl = 0)
    {
        $result = $this->client->set($key, $value, "EX", $ttl, "NX");
    }

    /**
     * Get the value of a key cached
     *
     * @param $key
     * @return string|null
     */
    public function get($key)
    {
        if ($this->client->exists($key)) {
            return $this->client->get($key);
        }
        return null;
    }

    /**
     * Delete the value cached based on a key
     *
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->client->del($key) ? true : false;
    }
}