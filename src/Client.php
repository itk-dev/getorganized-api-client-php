<?php

namespace ItkDev\GetOrganized;

use ItkDev\GetOrganized\Exception\InvalidServiceNameException;
use ItkDev\GetOrganized\Service\Cases;
use ItkDev\GetOrganized\Service\Documents;
use ItkDev\GetOrganized\Service\Tiles;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Client implements ClientInterface
{
    private string $username;
    private string $password;
    private string $baseUri;
    private ?HttpClientInterface $httpClient = null;

    /**
     * Construct a new client.
     *
     * Note: If passing a http client, the client must be fully configured to
     * send api requests, i.e. be authenticated and have the right api base uri
     * set. Use Client::createHttpClient() to get the default http client.
     *
     * For logging and debugging, a TraceableHttpClient
     * <https://github.com/symfony/http-client/blob/5.4/TraceableHttpClient.php>
     * can be used to wrap the default client, e.g.
     *
     * <code>
     * $httpClient = new TraceableHttpClient(Client::createHttpClient(…));
     * $httpClient->setLogger($this->logger);
     * $client = new Client(…, $httpClient);
     * </code>
     *
     * @param HttpClientInterface|null $httpClient optional http client used for api requests
     */
    public function __construct(string $username, string $password, string $baseUrl, ?HttpClientInterface $httpClient = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUri = $baseUrl;
        $this->httpClient = $httpClient;
    }

    /**
     * @throws InvalidServiceNameException
     */
    public function api(string $name): Service
    {
        switch ($name) {
            case 'documents':
                $service = new Documents($this);
                break;
            case 'tiles':
                $service = new Tiles($this);
                break;
            case 'cases':
                $service = new Cases($this);
                break;
            default:
                $message = sprintf('Undefined service "%s"', $name);
                throw new InvalidServiceNameException($message);
        }

        return $service;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->getHttpClient()->request($method, $url, $options);
    }

    /**
     * Create the default http client used for sending requests to the GetOrganized api.
     */
    public static function createHttpClient(string $username, string $password, string $baseUri): HttpClientInterface
    {
        return HttpClient::createForBaseUri($baseUri, [
            'auth_ntlm' => $username.':'.$password,
        ]);
    }

    protected function getHttpClient(): HttpClientInterface
    {
        if (null === $this->httpClient) {
            $this->httpClient = self::createHttpClient($this->username, $this->password, $this->baseUri);
        }

        return $this->httpClient;
    }
}
