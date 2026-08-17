<?php

declare(strict_types=1);

namespace AcMarche\Issep\Dto;

use AcMarche\Issep\Enums\IndiceEnum;
use Carbon\CarbonImmutable;
use Exception;

/**
 * One BelAQI reading, as returned by the /lastbelaqi and /belaqi endpoints.
 */
final readonly class Indice
{
    public function __construct(
        public int $configId,
        public ?int $networkId,
        public int $aqiValue,
        public string $pointName,
        public ?CarbonImmutable $ts,
        /**
         * True when the reading does not come from the station's own sensor but from the
         * fallback network. The legacy intranet labelled such a row "corrigé".
         */
        public bool $isFixed = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromApi(array $data): self
    {
        return new self(
            configId: (int) ($data['configId'] ?? 0),
            networkId: isset($data['networkId']) ? (int) $data['networkId'] : null,
            aqiValue: (int) ($data['aqiValue'] ?? IndiceEnum::NO_DATA->value),
            pointName: (string) ($data['pointName'] ?? ''),
            ts: self::parseTimestamp($data['ts'] ?? null),
        );
    }

    public function indice(): IndiceEnum
    {
        return IndiceEnum::fromAqiValue($this->aqiValue);
    }

    public function label(): string
    {
        return $this->indice()->label();
    }

    /**
     * "Bien (3)": the label and the raw index in a single string, which is how the stations
     * table shows a reading.
     */
    public function labelWithValue(): string
    {
        if (! $this->indice()->hasReading()) {
            return $this->label();
        }

        return "{$this->label()} ({$this->aqiValue})";
    }

    public function asFixed(): self
    {
        return new self(
            configId: $this->configId,
            networkId: $this->networkId,
            aqiValue: $this->aqiValue,
            pointName: $this->pointName,
            ts: $this->ts,
            isFixed: true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'config_id' => $this->configId,
            'network_id' => $this->networkId,
            'aqi_value' => $this->aqiValue,
            'point_name' => $this->pointName,
            'ts' => $this->ts,
            'is_fixed' => $this->isFixed,
            'label' => $this->label(),
            'label_with_value' => $this->labelWithValue(),
            'color' => $this->indice()->colorName(),
            'hex' => $this->indice()->hex(),
        ];
    }

    /**
     * The API sends a timestamp without an offset, expressed in its own timezone, so that is
     * the timezone it has to be read in.
     */
    private static function parseTimestamp(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || blank($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value, config('issep.timezone'));
        } catch (Exception) {
            return null;
        }
    }
}
