<?php

namespace IrisCrm\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use IrisCrm\Exception\AuthenticationRequiredException;
use IrisCrm\Exception\RuntimeException;
use IrisCrm\HttpClient\Message\ResponseMediator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Joseph Bielawski <stloyd@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Mitch Broadhead <mitch.broadhead@gmail.com>
 */
final class IrisCrmExceptionThrower implements Plugin
{
    /**
     * @return Promise
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $next($request)->then(function (ResponseInterface $response) use ($request) {
            if ($response->getStatusCode() < 400 || $response->getStatusCode() > 600) {
                return $response;
            }

            $remaining = ResponseMediator::getHeader($response, 'X-RateLimit-Remaining');
            if ((429 == $response->getStatusCode()) && null !== $remaining && 1 > $remaining) {
                $limit = (int) ResponseMediator::getHeader($response, 'X-RateLimit-Limit');
                $retry_after = (int) ResponseMediator::getHeader($response, 'Retry-After');
                throw new RateLimitExceededException($limit, $retry_after);
            }

            // @TODO: 4xx errors
            $content = ResponseMediator::getContent($response);
            if (401 == $response->getStatusCode()) {
                throw new AuthenticationRequiredException();
            }

            throw new RuntimeException(
                isset($content['message']) ? $content['message'] : $content,
                $response->getStatusCode()
            );
        });
    }
}
