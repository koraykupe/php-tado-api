<?php

use Auth\AuthenticationHandler;
use GuzzleHttp\Client;
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

        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../.env');
        $this->client = new GuzzleHttp\Client(['debug' => $_ENV['CONFIG'], 'headers' => ['Content-type' => 'application/json', 'Authorization' => 'Bearer ' . $authHandler->getToken()]]);
    }

    /**
     * @return mixed
     * @throws GuzzleException
     */
    public function getGeneralInfo()
    {
        $rawResponse = $this->client->get(self::API_BASE_URL . '/me');
        return json_decode($rawResponse->getBody()->getContents());
    }

    /**
     * @param $id
     * @return mixed
     * @throws GuzzleException
     */
    public function getHome($id)
    {
        $rawResponse = $this->client->get(self::API_BASE_URL . '/homes/' . $id);
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
        $rawResponse = $this->client->put(self::API_BASE_URL . '/homes/' . $homeId . '/zones/' . $zoneId . '/overlay', ['body' => json_encode($body)]);
        return json_decode($rawResponse->getBody()->getContents());
    }

}
