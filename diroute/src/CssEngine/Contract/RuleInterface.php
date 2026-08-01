<?php

namespace Diroute\CssEngine\Contract;

use Diroute\CssEngine\Parser\UtilityCandidate;

interface RuleInterface
{
    public function supports(UtilityCandidate $candidate): bool;
    public function generateCss(UtilityCandidate $candidate): string;
}
