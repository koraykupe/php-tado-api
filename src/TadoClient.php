<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class TadoClient
 */
class TadoClient
{
    public const API_AUTH_URL = 'https://auth.tado.com/oauth/token';
    public const API_BASE_URL = 'https://my.tado.com/api/v2';
    /**
     * @var Client
     */
    public    $client;
    protected $cache;

    /**
     * TadoClient constructor.
     */
    public function __construct()
    {
        $this->cache  = new FilesystemAdapter();
        $this->client = new GuzzleHttp\Client();
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../.env');
    }

    /**
     * @throws GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function fetchAccessToken($refreshtoken = null)
    {
        $rawResponse = $this->client->post(self::API_AUTH_URL . sprintf('?client_id=tado-web-app&client_secret=%s&grant_type=password&password=%s&scope=home.user&username=%s', $_ENV['CLIENT_SECRET'], $_ENV['PASSWORD'], $_ENV['USERNAME']));
        $response    = json_decode($rawResponse->getBody()->getContents());

        // $refreshToken = $response->refresh_token;
        $accessToken = $response->access_token;

        $token = $this->cache->getItem('tado.token2');
        if (!$token->isHit()) {
            $token->set($accessToken);
            $this->cache->save($token);
        }
        var_dump($accessToken);
        return $accessToken;
    }

    public function getToken($force = false)
    {
        if ($force === true || !$this->cache->hasItem('tado.token2')) {
            return $this->fetchAccessToken();
        }
        return $this->cache->getItem('tado.token2')->expiresAfter(599)->get();
    }

    public function getGeneralInfo()
    {
        try {
            $rawResponse = $this->client->get(self::API_BASE_URL . '/me', ['headers' => ['Authorization' => 'Bearer ' . $this->getToken()]]);
        } catch (ClientException $e) {
            $rawResponse = $this->client->get(self::API_BASE_URL . '/me', ['headers' => ['Authorization' => 'Bearer ' . $this->getToken(true)]]);
        }
        $response    = json_decode($rawResponse->getBody()->getContents());
        return $response;
    }

    public function getHomes($id)
    {
        try {
            $rawResponse = $this->client->get(self::API_BASE_URL . '/homes/' . $id, ['headers' => ['Authorization' => 'Bearer ' . $this->getToken()]]);
        } catch (ClientException $e) {
            $rawResponse = $this->client->get(self::API_BASE_URL . '/homes/' . $id, ['headers' => ['Authorization' => 'Bearer ' . $this->getToken(true)]]);
        }
        $response    = json_decode($rawResponse->getBody()->getContents());
        return $response;
    }

    public function setTemperature($homeId, $zoneId, bool $power, int $temperature = null, int $timer = null)
    {
        $body = [
            'setting' => [
                'type' => 'HEATING',
                'power' => $power ? 'ON' : 'OFF',
                'temperature' => [
                    'celsius' => $temperature
                ]
            ],
            'termination' => [
                'type' => $timer ? 'TIMER' : 'MANUAL'
            ]
        ];
        if ($timer) {
            $body['termination']['durationInSeconds'] = $timer * 60;
        }
        try {
            $rawResponse = $this->client->put(self::API_BASE_URL . '/homes/' . $homeId . '/zones/' . $zoneId . '/overlay', ['headers' => ['Authorization' => 'Bearer ' . $this->getToken()], 'body' => json_encode($body)]);
        } catch (GuzzleException $e) {
            $rawResponse = $this->client->put(self::API_BASE_URL . '/homes/' . $homeId . '/zones/' . $zoneId . '/overlay', ['headers' => ['Authorization' => 'Bearer ' . $this->getToken(true)], 'body' => json_encode($body)]);
        }
        $response    = json_decode($rawResponse->getBody()->getContents());
        return $response;
    }

}
