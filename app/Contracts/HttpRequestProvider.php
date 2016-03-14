<?php


namespace App\Contracts;

interface HttpRequestProvider {
    
    /**
     * Performs HTTP request via PHP stream context features
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param array $headers
     * @return \Illuminate\Http\Response
     */
    public function request($method = 'GET', $url = '', $data = null, $headers = []);
}
