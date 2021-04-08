<?php

namespace IrisCrm;

use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin;
use Http\Discovery\Psr17FactoryDiscovery;
use IrisCrm\Api\AbstractApi;
use IrisCrm\Exception\BadMethodCallException;
use IrisCrm\Exception\InvalidArgumentException;
use IrisCrm\HttpClient\Builder;
use IrisCrm\HttpClient\Plugin\Authentication;
use IrisCrm\HttpClient\Plugin\History;
use IrisCrm\HttpClient\Plugin\IrisCrmExceptionThrower;
use IrisCrm\HttpClient\Plugin\DomainRequiredPlugin;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * An unofficial IrisCRM API heavily inspired by http://github.com/KnpLabs/php-github-api
 */
class Client
{
    /**
     * Authenticate using an X-API-KEY header token
     * @var string
     */
    const AUTH_ACCESS_TOKEN = 'access_token_header';

    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @var Builder
     */
    private $httpClientBuilder;

    /**
     * @var string
     */
    private $domain;

    /**
     * Instantiate a new IrisCrm client.
     *
     * @param Builder|null $httpClientBuilder
     * @param string|null  $apiVersion
     * @param string|null  $enterpriseUrl
     */
    public function __construct(Builder $httpClientBuilder = null, $apiVersion = null, $enterpriseUrl = null)
    {
        $this->responseHistory = new History();
        $this->httpClientBuilder = $builder = $httpClientBuilder ?? new Builder();

        $builder->addPlugin(new IrisCrmExceptionThrower());
        $builder->addPlugin(new Plugin\HistoryPlugin($this->responseHistory));
        $builder->addPlugin(new Plugin\RedirectPlugin());
        $builder->addPlugin(new Plugin\HeaderDefaultsPlugin([
            'User-Agent' => 'php-iriscrm-api (http://github.com/mbroadhead/php-iriscrm-api)',
        ]));
        $builder->addPlugin(new DomainRequiredPlugin());
    }

    /**
     * Create an IrisCrm\Client using a HTTP client.
     *
     * @param ClientInterface $httpClient
     *
     * @return Client
     */
    public static function createWithHttpClient(ClientInterface $httpClient): self
    {
        $builder = new Builder($httpClient);

        return new self($builder);
    }

    public function api($name): AbstractApi
    {
        switch($name) {
            case 'lead':
            case 'leads':
                $api = new Api\Lead($this);
                break;
            default:
                throw new InvalidArgumentException(sprintf('Undefined api instance called: "%s"', $name));
        }

        return $api;
    }

    /**
     * Authenticate a user for all next requests.
     *
     * @param string      $tokenOrLogin GitHub private token/username/client ID
     * @param string|null $password     GitHub password/secret (optionally can contain $authMethod)
     * @param string|null $authMethod   One of the AUTH_* class constants
     *
     * @throws InvalidArgumentException If no authentication method was given
     *
     * @return void
     */
    public function authenticate($tokenOrLogin, $password = null, $authMethod = null): void
    {
        if (null === $authMethod && self::AUTH_ACCESS_TOKEN === $password) {
            $authMethod = $password;
            $password = null;
        }

        if (null === $authMethod) {
            throw new InvalidArgumentException('You need to specify authentication method!');
        }

        $this->getHttpClientBuilder()->removePlugin(Authentication::class);
        $this->getHttpClientBuilder()->addPlugin(new Authentication($tokenOrLogin, $password, $authMethod));
    }

    public function setDomain($domain): void
    {
        $apiVersion = $this->apiVersion ?: 'v1';
        $this->getHttpClientBuilder()->removePlugin(Plugin\AddHostPlugin::class);
        $this->getHttpClientBuilder()->removePlugin(DomainRequiredPlugin::class);
        $this->getHttpClientBuilder()
             ->addPlugin(
                 new Plugin\BaseUriPlugin(
                     Psr17FactoryDiscovery::findUriFactory()->createUri(
                         sprintf('https://%s/api/%s', $domain, $apiVersion)
                     )
                 )
             );
    }

    public function getApiVersion(): string
    {
        $this->apiVersion;
    }

    /**
     * Add a cache plugin to cache responses locally.
     *
     * @param CacheItemPoolInterface $cachePool
     * @param array                  $config
     *
     * @return void
     */
    public function addCache(CacheItemPoolInterface $cachePool, array $config = []): void
    {
        $this->getHttpClientBuilder()->addCache($cachePool, $config);
    }

    /**
     * Remove the cache plugin.
     *
     * @return void
     */
    public function removeCache(): void
    {
        $this->getHttpClientBuilder()->removeCache();
    }

    /**
     * @param string $name
     * @param array  $args
     *
     * @return AbstractApi
     */
    public function __call($name, $args): AbstractApi
    {
        try {
            return $this->api($name);
        } catch (InvalidArgumentException $e) {
            throw new BadMethodCallException(sprintf('Undefined method called: "%s"', $name));
        }
    }

    /**
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    public function getLastResponse(): ?ResponseInterface
    {
        return $this->responseHistory->getLastResponse();
    }

    /**
     * @return HttpMethodsClientInterface
     */
    public function getHttpClient(): HttpMethodsClientInterface
    {
        return $this->getHttpClientBuilder()->getHttpClient();
    }

    /**
     * @return Builder
     */
    protected function getHttpClientBuilder(): Builder
    {
        return $this->httpClientBuilder;
    }
}
