<?php

declare(strict_types=1);


namespace vlaim\PioCheck;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use stdClass;
use vlaim\PioCheck\dto\Application;

class PioApi
{
    protected const API_URL = 'https://api-przybysz.duw.pl/';
    protected const TOKEN_CACHE_PATH = __DIR__.'/../.token';

    protected Client $client;

    private bool $forceObtainToken = false;

    public function __construct(
        protected string $login,
        protected string $password,
    )
    {
        $this->client = new Client([
            'base_uri' => self::API_URL,
            'verify' => false,
        ]);
    }

    private function getCachePath(): string
    {
        return self::TOKEN_CACHE_PATH.'_'.$this->login;
    }


    protected function obtainToken(): string
    {
        $response = $this->client->post('/api/v1/token/obtain', [
            RequestOptions::JSON => [
                'login' => $this->login,
                'password' => $this->password,
            ],
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json, text/plain, */*',
            ]
        ]);

        /** @var stdClass */
        $data = json_decode($response->getBody()->getContents());

        if(!empty($data->token)){
            $token = (string) $data->token;
            file_put_contents($this->getCachePath(), $token);
            return $token;
        }

        throw new \RuntimeException('An error occurred while obtaining a new token');


    }


    public function getToken(): string
    {
        if ($this->forceObtainToken) {
            $this->obtainToken();
        }
        return (string) @file_get_contents($this->getCachePath());
    }



    public function getCommuniques(int $id): array
    {
        $token = $this->getToken();

        try {
            $response = $this->client->get("/api/v1/communiques?application={$id}&pagination=false", [
                'headers' => [
                    'Authorization' => "Bearer {$token}"
                ]
            ]);

            /** @var stdClass */
            $responseData = json_decode($response->getBody()->__toString());
            /** @var array */
            $result = $responseData->{'hydra:member'} ?? [];
            return $result;
        } catch (RequestException $exception) {
            if ($exception->getCode() === 401) {
                $this->forceObtainToken = true;
                return $this->getCommuniques($id);
            }


            throw $exception;
        }

    }


    public function getApplication(int $id): Application
    {
        $token = $this->getToken();

        try {
            $response = $this->client->get("/api/v1/applications/{$id}", [
                RequestOptions::HEADERS => [
                    'Authorization' => "Bearer {$token}"
                ]
            ]);

            /** @var stdClass */
            $responseData = json_decode($response->getBody()->__toString());

            return new Application($responseData);

        } catch (RequestException $exception) {
            if ($exception->getCode() === 401) {
                $this->forceObtainToken = true;
                return $this->getApplication($id);
            }

            throw $exception;
        }

    }




}