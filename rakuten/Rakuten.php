<?php

require_once("../vendor/autoload.php");

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;

class Rakuten
{
    const SEARCH_ORDER = 'order/searchOrder';

    protected $httpClient;

    protected $secretKey;

    protected $path = 'https://api.rms.rakuten.co.jp/es/2.0/';

    public function __construct($secretKey, $options = [])
    {
        $this->secretKey = $secretKey;
        var_dump(HttpClient);
        $this->httpClient = $options instanceof HttpClient ? $options : new HttpClient($this->httpOptionsDefaults($options));
    }

    protected function httpOptionsDefaults($options = [])
    {
        return array_merge([
            'base_uri' => $this->path,
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8'
            ],
        ], $options);
    }

    public function request($method, $endpoint, array $params = [])
    {
        $method = strtoupper($method);
        $endpoint = $this->buildEndpoint($endpoint, $params);
        $options = $this->requestOptions($method, $params);

        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
        } catch (ClientException $e) {
            throw new Exception($e);
        }

        return $response;
    }

    protected function buildEndpoint($endpoint, &$params = [])
    {
        $endpoint = preg_replace('/^\//', '', $endpoint);

        preg_match_all('/\{(.+?)\}/', $endpoint, $matches);

        if (empty($matches[0])) {
            return $endpoint;
        }

        foreach ($matches[1] as $match) {
            if (isset($params[$match])) {
                $endpoint = preg_replace("/\{$match\}/", $params[$match], $endpoint);
                unset($params[$match]);
            } else {
                throw new Exception();
            }
        }

        return $endpoint;
    }

    protected function requestOptions($method, array $params)
    {
        $options = [
            'headers' => [
                'Authorization' => 'ESA' . $this->secretKey
            ]
        ];

        if ($method == 'GET') {
            $options['query'] = $params;
        } else {
            $options['form_params'] = $params;
        }

        return $options;
    }

    public function secretKey($secretKey = null)
    {
        if (is_string($secretKey)) {
            $this->secretKey = $secretKey;
        }

        return $this->secretKey;
    }

    public function httpClient($httpClient = null)
    {
        if ($httpClient instanceof HttpClient) {
            $this->httpClient = $httpClient;
        }

        return $this->httpClient;
    }
}