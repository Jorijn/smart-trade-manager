<?php

namespace App\Controller;

use App\Bus\Message\Query\ActiveTradesQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class TradeController
{
    use HandleTrait;

    /**
     * @param MessageBusInterface $queryBus
     */
    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    /**
     * @return JsonResponse
     */
    public function getActiveTrades(): JsonResponse
    {
        return new JsonResponse($this->handle(new ActiveTradesQuery()));
    }
}
