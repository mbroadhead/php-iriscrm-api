<?php

namespace IrisCrm\HttpClient\Plugin;

use IrisCrm\Client;
use IrisCrm\Exception\RuntimeException;
use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

/**
 * Add authentication to the request.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class Authentication implements Plugin
{
    /**
     * @var string
     */
    private $tokenOrLogin;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string|null
     */
    private $method;

    /**
     * @param string      $tokenOrLogin GitHub private token/username/client ID
     * @param string|null $password     GitHub password/secret (optionally can contain $method)
     * @param string|null $method       One of the AUTH_* class constants
     */
    public function __construct(string $tokenOrLogin, ?string $password, ?string $method)
    {
        $this->tokenOrLogin = $tokenOrLogin;
        $this->password = $password;
        $this->method = $method;
    }

    /**
     * @return Promise
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $request = $request->withHeader(
            'X-API-KEY',
            $this->getAuthorizationHeader()
        );

        return $next($request);
    }

    private function getAuthorizationHeader(): string
    {
        switch ($this->method) {
            case Client::AUTH_ACCESS_TOKEN:
                return $this->tokenOrLogin;
            default:
                throw new RuntimeException(sprintf('%s not yet implemented', $this->method));
        }
    }
}
