<?php

require_once("../vendor/autoload.php");
require_once("../chatwork/Response.php");

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;

class Chatwork
{
    const GET_ME                   = 'me';
    const GET_MY_STATUS            = 'my/status';
    const GET_MY_TASKS             = 'my/tasks';
    const GET_CONTACTS             = 'contacts';
    const GET_ROOMS                = 'rooms';
    const POST_ROOMS               = 'rooms';
    const PUT_ROOM                 = 'rooms/{room_id}';
    const DELETE_ROOM              = 'rooms/{room_id}';
    const GET_ROOM                 = 'rooms/{room_id}';
    const GET_ROOM_MEMBERS         = 'rooms/{room_id}/members';
    const PUT_ROOM_MEMBERS         = 'rooms/{room_id}/members';
    const GET_ROOM_MESSAGES        = 'rooms/{room_id}/messages';
    const POST_ROOM_MESSAGES       = 'rooms/{room_id}/messages';
    const PUT_ROOM_MESSAGES_READ   = 'rooms/{room_id}/messages/read';
    const PUT_ROOM_MESSAGES_UNREAD = 'rooms/{room_id}/messages/unread';
    const GET_ROOM_MESSAGE         = 'rooms/{room_id}/messages/{message_id}';
    const PUT_ROOM_MESSAGE         = 'rooms/{room_id}/messages/{message_id}';
    const DELETE_ROOM_MESSAGE      = 'rooms/{room_id}/messages/{message_id}';
    const GET_ROOM_TASKS           = 'rooms/{room_id}/tasks';
    const POST_ROOM_TASKS          = 'rooms/{room_id}/tasks';
    const GET_ROOM_TASK            = 'rooms/{room_id}/tasks/{task_id}';
    const GET_ROOM_FILES           = 'rooms/{room_id}/files';
    const POST_ROOM_FILES          = 'rooms/{room_id}/files';
    const GET_ROOM_FILE            = 'rooms/{room_id}/files/{file_id}';
    const GET_ROOM_LINK            = 'rooms/{room_id}/link';
    const POST_ROOM_LINK           = 'rooms/{room_id}/link';
    const PUT_ROOM_LINK            = 'rooms/{room_id}/link';
    const DELETE_ROOM_LINK         = 'rooms/{room_id}/link';
    const GET_INCOMING_REQUESTS    = 'incoming_requests';
    const PUT_INCOMING_REQUEST     = 'incoming_requests/{request_id}';
    const DELETE_INCOMING_REQUEST  = 'incoming_requests/{request_id}';

    /**
     * HttpClient
     */
    protected $httpClient;

    /**
     * string 
     */
    protected $token;

    /**
     * string
     */
    protected $uri = 'https://api.chatwork.com/v2/';

    /**
     * @param string|null $token
     * @param array|HttpClient $options
     */
    public function __construct($token, $options = [])
    {
        $this->token = $token;

        $this->httpClient = $options instanceof HttpClient ? $options : new HttpClient($this->httpOptionsDefaults($options));
    }

    protected function httpOptionsDefaults(array $options)
    {
        return array_merge([
            'base_uri' => $this->uri,
            'headers' => [
                'User-Agent' => 'chatwork-php',
                'Accept'     => 'application/json',
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

        return new Response($response, [
            'method' => $method,
            'endpoint' => $endpoint,
            'params' => $params,
            'token' => $this->token,
        ]);
    }

    protected function buildEndpoint($endpoint, array &$params)
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
                'X-ChatWorkToken' => $this->token
            ]
        ];

        if ($method == 'GET') {
            $options['query'] = $params;
        } else {
            $options['form_params'] = $params;
        }

        return $options;
    }

    public function token($token = null)
    {
        if (is_string($token)) {
            $this->token = $token;
        }

        return $this->token;
    }

    /**
     * @param null|HttpClient $httpClient
     * @return HttpClient
     */
    public function httpClient($httpClient = null)
    {
        if ($httpClient instanceof HttpClient) {
            $this->httpClient = $httpClient;
        }

        return $this->httpClient;
    }

    public function __call($name, $args)
    {
        $chars = preg_split('/([A-Z][a-z]*)/', $name, null, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);

        $constant = 'static::' . strtoupper(implode('_', $chars));

        if (!$constant || !defined($constant)) {
            throw new Exception('Class constant does not exist');
        }

        $endpoint = constant($constant);
        $params = isset($args[0]) ? $args[0] : [];

        return $this->request($chars[0], $endpoint, $params);
    }
}
