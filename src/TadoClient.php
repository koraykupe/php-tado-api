<?php

use Auth\AuthenticationHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class TadoClient
 */
class TadoClient
{
    public const API_BASE_URL = 'https://my.tado.com/api/v2';
    /**
     * @var Client
     */
    public $client;

    /**
     * TadoClient constructor.
     */
    public function __construct()
    {
        $authHandler  = new AuthenticationHandler();
        $this->client = new GuzzleHttp\Client(['headers' => ['Authorization' => 'Bearer ' . $authHandler->getToken()]]);

        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../.env');
    }

    /**
     * @return mixed
     * @throws GuzzleException
     */
    public function getGeneralInfo()
    {
        try {
            $rawResponse = $this->client->get(self::API_BASE_URL . '/me');
        } catch (ClientException $e) {
            $rawResponse = $this->client->get(self::API_BASE_URL . '/me');
        }
        return json_decode($rawResponse->getBody()->getContents());
    }

    /**
     * @param $id
     * @return mixed
     * @throws GuzzleException
     */
    public function getHome($id)
    {
        try {
            $rawResponse = $this->client->get(self::API_BASE_URL . '/homes/' . $id);
        } catch (ClientException $e) {
            // @todo
        }
        return json_decode($rawResponse->getBody()->getContents());
    }

    /**
     * @param          $homeId
     * @param          $zoneId
     * @param bool     $power
     * @param int|null $temperature
     * @param int|null $timer
     * @return mixed
     * @throws GuzzleException
     */
    public function setTemperature($homeId, $zoneId, bool $power, int $temperature = null, int $timer = null)
    {
        $body = [
            'setting'     => [
                'type'        => 'HEATING',
                'power'       => $power ? 'ON' : 'OFF',
                'temperature' => [
                    'celsius' => $temperature,
                ],
            ],
            'termination' => [
                'type' => $timer ? 'TIMER' : 'MANUAL',
            ],
        ];
        if ($timer) {
            $body['termination']['durationInSeconds'] = $timer * 60;
        }
        try {
            $rawResponse = $this->client->put(self::API_BASE_URL . '/homes/' . $homeId . '/zones/' . $zoneId . '/overlay', ['body' => json_encode($body)]);
        } catch (GuzzleException $e) {
            // @todo
        }
        return json_decode($rawResponse->getBody()->getContents());
    }

}
