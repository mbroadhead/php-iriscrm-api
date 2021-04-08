<?php

namespace IrisCrm\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use IrisCrm\Exception\DomainRequiredException;

final class DomainRequiredPlugin implements Plugin
{
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        throw new DomainRequiredException(
            'You must set the domain in your client! Usually it is'
            . ' "{your_company}.iriscrm.com".'
            . ' See: IrisCrm\Client::setDomain()'
        );
    }
}
