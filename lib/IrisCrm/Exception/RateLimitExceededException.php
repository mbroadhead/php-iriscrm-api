<?php

namespace IrisCrm\Exception;

use Throwable;

class RateLimitExceededException extends RuntimeException
{
    /* @var int */
    private $limit;

    /* @var int */
    private $retry_after;

    /**
     * @param int            $limit
     * @param int            $retry_after
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(int $limit = 500, int $retry_after = 60, int $code = 0, Throwable $previous = null)
    {
        $this->limit = (int) $limit;
        $this->retry_after = (int) $retry_after;
        $limit_per_sec = $this->limit * $this->retry_after

        parent::__construct(sprintf('You have reached the IrisCrm %s req/min limit! You may retry in %d second(s).', $limit, $retry_after), $code, $previous);
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getResetTime(): int
    {
        return $this->reset;
    }
}

