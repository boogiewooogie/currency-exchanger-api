<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\When;

class ConversionDto
{
    public function __construct(
        #[NotBlank(message: 'Missing currency_from')]
        #[SerializedName('currency_from')]
        protected readonly string $currencyFrom = '',
        #[NotBlank(message: 'Missing currency_to')]
        #[NotEqualTo(propertyPath: 'currencyFrom', message: 'Currencies should not be equal')]
        #[When('this.getCurrencyFrom() !== "USD"', [new EqualTo('USD', message: 'One of currencies must be "USD"')])]
        #[SerializedName('currency_to')]
        protected readonly string $currencyTo = '',
        #[Positive(message: 'Value should be positive')]
        #[NotBlank(message: 'Missing value')]
        protected readonly float $value = 0,
        #[Groups(['out-coming'])]
        #[SerializedName('converted_value')]
        protected ?float $convertedValue = null,
        #[Groups(['out-coming'])]
        protected ?float $rate = null
    ) {
    }

    /**
     * @return string
     */
    public function getCurrencyFrom(): string
    {
        return $this->currencyFrom;
    }

    /**
     * @return string
     */
    public function getCurrencyTo(): string
    {
        return $this->currencyTo;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return max($this->value, $_ENV['MINIMAL_CONVERSION_VALUE']);
    }

    /**
     * @return float|null
     */
    public function getConvertedValue(): ?float
    {
        return $this->convertedValue;
    }

    /**
     * @param float|null $convertedValue
     * @return $this
     */
    public function setConvertedValue(?float $convertedValue): self
    {
        $this->convertedValue = $convertedValue;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getRate(): ?float
    {
        return $this->rate;
    }

    /**
     * @param float|null $rate
     * @return $this
     */
    public function setRate(?float $rate): self
    {
        $this->rate = $rate;
        return $this;
    }
}