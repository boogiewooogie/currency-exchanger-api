<?php

namespace App\DTO;

class RateDto
{
    public function __construct(
        protected string $id,
        protected string $symbol,
        protected string $type,
        protected string $rateUsd,
        protected ?string $currencySymbol = null
    ) {
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     * @return $this
     */
    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getRateUsd(): string
    {
        return $this->rateUsd;
    }

    /**
     * @param string $rateUsd
     * @return $this
     */
    public function setRateUsd(string $rateUsd): self
    {
        $this->rateUsd = $rateUsd;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrencySymbol(): ?string
    {
        return $this->currencySymbol;
    }

    /**
     * @param string|null $currencySymbol
     * @return $this
     */
    public function setCurrencySymbol(?string $currencySymbol): self
    {
        $this->currencySymbol = $currencySymbol;
        return $this;
    }
}