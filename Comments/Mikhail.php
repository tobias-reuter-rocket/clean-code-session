<?php
/**
 * and the answer is:
 * if first field evaluates (loosely) to true (= is not empty),
 * then second field must evaluate (loosely) to false (= empty)
 * in other words, only one of them can be not empty at the same time :)
 */


$fixedAmount->addValidator(
    new ValidateOnValue(
        'percentagePercent', true, new InclusionIn(['domain' => [false]])
    )
);
$percentagePercent->addValidator(
    new ValidateOnValue(
        'fixedAmount', true, new InclusionIn(['domain' => [false]])
    )
);

