<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class JsonRequestBodyEventListener
{
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($this->isAvailable($request) === false) {
            return;
        }

        if ($this->transform($request) === false) {
            $response = new Response('Unable to parse request.', 400);
            $event->setResponse($response);
        }
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isAvailable(Request $request): bool
    {
        return $request->getContentType() === 'json' && $request->getContent();
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function transform(Request $request): bool
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        if (is_array($data)) {
            $request->request->replace($data);
        }

        return true;
    }
}
