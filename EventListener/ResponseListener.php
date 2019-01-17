<?php

namespace ShortCode\EventListener;

use Maiorano\Shortcodes\Library\SimpleShortcode;
use Maiorano\Shortcodes\Manager\ShortcodeManager;
use ShortCode\Event\ShortCodeEvent;
use ShortCode\Model\ShortCode;
use ShortCode\Model\ShortCodeQuery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseListener implements EventSubscriberInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [['dispatchShortCodeEvents', 64]]
        ];
    }

    public function dispatchShortCodeEvents(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse || $response instanceof RedirectResponse) {
            return;
        }

        $dispatcher = $this->eventDispatcher;

        $simpleShortCodes = [];

        $shortCodes = ShortCodeQuery::create()
            ->filterByActive(1)
            ->find();


        /** @var ShortCode $shortCode */
        foreach ($shortCodes as $shortCode) {
            $simpleShortCodes[$shortCode->getTag()] = new SimpleShortcode($shortCode->getTag(), null, function ($content, $attributes) use ($shortCode, $dispatcher) {
                $shortCodeEvent = new ShortCodeEvent($content, $attributes);
                $dispatcher->dispatch($shortCode->getEvent(), $shortCodeEvent);
                return $shortCodeEvent->getResult();
            });
        }

        $manager = new ShortcodeManager($simpleShortCodes);

        $results = $manager->doShortCode($response->getContent(), null, true);
        $response->setContent($results);
    }
}