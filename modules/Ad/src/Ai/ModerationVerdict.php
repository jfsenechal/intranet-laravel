<?php

declare(strict_types=1);

namespace AcMarche\Ad\Ai;

final readonly class ModerationVerdict
{
    /**
     * @param  list<string>  $reasons  why the ad was rejected, addressed to its author
     */
    public function __construct(
        public bool $isCompliant,
        public array $reasons = [],
    ) {}
}
