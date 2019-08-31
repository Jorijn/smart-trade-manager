<?php

namespace App\Bus\Message\Query;

use App\Bus\Middleware\CacheInterface;

class SymbolListQuery implements CacheInterface
{
    /**
     * {@inheritDoc}
     */
    public function getExpiresAfter()
    {
        return 86400;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheKey(): string
    {
        return str_replace('\\', '_', __CLASS__);
    }
}
