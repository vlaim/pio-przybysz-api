<?php

declare(strict_types=1);


namespace vlaim\PioCheck;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Phpfastcache\Drivers\Files\Config;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Phpfastcache\Helper\Psr16Adapter;
use stdClass;
use vlaim\PioCheck\dto\Application;

class PioApi
{
    protected string $apiUrl = 'https://api-przybysz.duw.pl/';

    protected Client $client;

    protected ?Psr16Adapter $adapter = null;

    private bool $forceObtainToken = false;

    public function __construct(
        protected string $login,
        protected string $password,
    )
    {
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'verify' => false,
        ]);
    }

    public function setCacheAdapter(Psr16Adapter $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }


    public function getCacheAdapter(): Psr16Adapter
    {
        try {
            if ($this->adapter === null) {
                return new Psr16Adapter('Files', new Config([
                    'path' => __DIR__ . '/../cache',
                ]));
            }
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Failed to init cache adapter');
        }

        return $this->adapter;
    }

    public function setApiUrl(string $apiUrl): self
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }


    /**
     * @throws PhpfastcacheSimpleCacheException
     * @throws GuzzleException
     */
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

        /** @var stdClass $data */
        $data = json_decode($response
            ->getBody()
            ->getContents()
        );

        if (!empty($data->token)) {
            $token = (string)$data->token;
            $this
                ->getCacheAdapter()
                ->set($this->login, $token, 60 * 60);

            return $token;
        }

        throw new \RuntimeException('An error occurred while obtaining a new token');
    }

    /**
     * @throws PhpfastcacheSimpleCacheException
     * @throws GuzzleException
     */
    public function getToken(): string
    {
        if ($this->forceObtainToken) {
            $this->obtainToken();
        }
        return (string)$this
            ->getCacheAdapter()
            ->get($this->login);
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