<?php

namespace Auth;

use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Dotenv\Dotenv;

class AuthenticationHandler
{
    public const API_AUTH_URL = 'https://auth.tado.com/oauth/token';
    public const TOKEN_NAME   = 'tado_token2';
    public const EXPIRE_TIME  = 598; // minutes

    private   $clientSecret;
    private   $username;
    private   $password;
    protected $cache;

    /**
     * AuthenticationHandler constructor.
     */
    public function __construct()
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../../.env');
        $this->clientSecret = $_ENV['CLIENT_SECRET'];
        $this->username     = $_ENV['USERNAME'];
        $this->password     = $_ENV['PASSWORD'];
        $this->cache        = new FilesystemAdapter();
    }

    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function fetchToken(): string
    {
        $response = (new Client)->request('POST', self::API_AUTH_URL . sprintf('?client_id=tado-web-app&client_secret=%s&grant_type=password&password=%s&scope=home.user&username=%s', $_ENV['CLIENT_SECRET'], $_ENV['PASSWORD'], $_ENV['USERNAME']), [
            'form_params' => [
                'client_secret' => $this->clientSecret,
                'username'      => $this->username,
                'password'      => $this->password,
            ],
        ]);
        $response = json_decode($response->getBody()->getContents(), true);
        return $response['access_token'] ?? "";
    }

    public function getToken()
    {
        $token = $this->cache->getItem(self::TOKEN_NAME);

        if (!$token->isHit()) {
            $token->set($this->fetchToken());
            $token->expiresAfter(self::EXPIRE_TIME);
            $this->cache->save($token);
        }

        return $token->get();
    }

}
