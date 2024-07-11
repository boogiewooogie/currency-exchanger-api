<?php

namespace App\Controller;

use App\DTO\ConversionDto;
use App\DTO\RateDto;
use App\Exception\BadRequestException;
use App\Exception\InternalException;
use App\Response\Response;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CacheItemPoolInterface $cache,
        private readonly HttpClientInterface $coincapClient,
        private readonly SerializerInterface|NormalizerInterface|DenormalizerInterface $serializer,
    ) {
    }

    /**
     * Получение значений курсов валют
     * @param string|null $currency - Список офциальных кратких наименований валют
     * @return BaseResponse
     * @throws BadRequestException
     */
    #[Route('/rates', name: 'currency_getter', methods: ['GET'], condition: "service('token_validator').validate()")]
    public function rates(#[MapQueryParameter] ?string $currency = null): BaseResponse
    {
        $currencies = isset($currency) ? explode(',', $currency) : $this->cache->get('available_currencies', $this->getAvailableCurrencies(...));
        foreach ($currencies as $currency) {
            $currency = trim($currency);
            $rates[$currency] = $this->getCurrencyRateFromCache($currency);
        }
        asort($rates, SORT_NUMERIC);
        return new Response($rates);
    }

    /**
     * Получение условий обмена валюты
     * @param ConversionDto $conversionDto - Информация о запрашиваемом обмене
     * @return BaseResponse
     * @throws BadRequestException|ExceptionInterface
     */
    #[Route('/convert', name: 'currency_converter', methods: ['POST'], condition: "service('token_validator').validate()")]
    public function convert(#[MapRequestPayload] ConversionDto $conversionDto): BaseResponse
    {
        $conversionRate = $this->getCurrencyRateFromCache($conversionDto->getCurrencyFrom()) / $this->getCurrencyRateFromCache($conversionDto->getCurrencyTo()) / (1 + $_ENV['CONVERSION_COMMISSION']);
        $conversionDto->setRate($conversionRate)
            ->setConvertedValue(round($conversionDto->getValue() * $conversionRate, 10));
        return new Response($this->serializer->normalize($conversionDto, context: ['out-coming']));
    }

    /**
     * Поулчение списка поддерживаемых валют
     * @return array<string>
     * @throws InternalException
     */
    private function getAvailableCurrencies(): array
    {
        $availableCurrencies = array_map($this->cacheCurrencyRate(...), $this->getCurrenciesRatesFromClient());
        $this->cache->get('cache_loaded', fn(): true => true);
        return $availableCurrencies;
    }

    /**
     * Сохранение занчений курсов валют в кэш
     * @return true
     * @throws InternalException
     */
    private function loadCache(): true
    {
        $availableCurrencies = array_map($this->cacheCurrencyRate(...), $this->getCurrenciesRatesFromClient());
        $this->cache->get('available_currencies', fn() => $availableCurrencies);
        return true;
    }

    /**
     * Получение информации о валютах из стороннего сервиса
     * @return array<RateDto>
     * @throws InternalException
     */
    private function getCurrenciesRatesFromClient(): array
    {
        try {
            $ratesServiceResponse = $this->coincapClient->request('GET', '/v2/rates');
            return $this->serializer->deserialize($ratesServiceResponse->getContent(), 'App\DTO\RateDto[]', 'json', [UnwrappingDenormalizer::UNWRAP_PATH => '[data]']);
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $exception) {
            $this->logger->critical($exception);
            throw new InternalException(previous: $exception);
        }
    }

    /**
     * Сохранение значения курса валюты в кэш
     * @param RateDto $rateDto - Информация о валюте, полученная из стороннего сервиса
     * @return string
     */
    private function cacheCurrencyRate(RateDto $rateDto): string
    {
        $this->cache->get($rateDto->getSymbol(), fn(): float => $rateDto->getRateUsd() * (1 + $_ENV['CONVERSION_COMMISSION']));
        return $rateDto->getSymbol();
    }

    /**
     * Получение значения курса валюты из кэша
     * @param string $currency - Официальное краткое наименование валюты
     * @return float
     * @throws BadRequestException
     */
    private function getCurrencyRateFromCache(string $currency): float
    {
        try {
            $this->cache->get('cache_loaded', $this->loadCache(...));
            return $this->cache->get($currency, fn() => throw new BadRequestException('Unsupported currency: "' . $currency . '"'));
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception);
            throw new BadRequestException('Invalid currency symbol', previous: $exception);
        }
    }
}
