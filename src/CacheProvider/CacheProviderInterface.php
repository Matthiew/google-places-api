<?php

namespace SKAgarwal\GoogleApi\CacheProvider;

interface CacheProviderInterface
{
    /**
     * Set the key and the value to cache and you can add a ttl
     *
     * @param $key
     * @param $value
     * @param $ttl
     * @return mixed
     */
    public function set($key, $value, $ttl = 0);

    /**
     * Get the value of a key cached
     *
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * Delete the value cached based on a key
     *
     * @param $key
     * @return bool
     */
    public function delete($key);
}