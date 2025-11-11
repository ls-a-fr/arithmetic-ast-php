<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Tokens;

/**
 * A SingleToken is a scalar value: "1", "12pt".
 */
class SingleToken extends Token
{
    public function evaluate(): float|string
    {
        if ($this->hasBeenEvaluated() === false) {
            $this->setEvaluationResult($this->token);
        }

        return $this->getEvaluationResult();
    }
}
