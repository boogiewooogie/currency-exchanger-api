<?php

namespace App\EventSubscriber;

use App\Exception\BadRequestException;
use App\Exception\ExceptionInterface;
use App\Exception\InternalException;
use App\Exception\MethodNotAllowedException;
use App\Response\StatusThesaurus;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

readonly class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function process(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!in_array(ExceptionInterface::class, class_implements($throwable))) {
            $throwable = match (get_class($throwable)) {
                UnprocessableEntityHttpException::class => new BadRequestException($throwable->getMessage(), previous: $throwable),
                MethodNotAllowedHttpException::class => new MethodNotAllowedException(previous: $throwable),
                NotFoundHttpException::class => new BadRequestException('Invalid method name', previous: $throwable),
                default => new InternalException(previous: $throwable)
            };
            $event->setThrowable($throwable);
        }
    }

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function log(ExceptionEvent $event): void
    {
        $this->logger->error($event->getThrowable());
    }

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function notify(ExceptionEvent $event): void
    {
        $event->setResponse(new Response($this->serializer->serialize([
            'status' => StatusThesaurus::Error,
            'code' => $event->getThrowable()->getCode(),
            'message' => $event->getThrowable()->getMessage()
        ], 'json')));
    }

    /**
     * @return array<array>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['process', 10],
                ['log', 0],
                ['notify', -10],
            ]
        ];
    }
}