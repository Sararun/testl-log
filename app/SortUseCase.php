<?php

namespace App;

use OpenTelemetry\API\Instrumentation\SpanAttribute;
use OpenTelemetry\API\Instrumentation\WithSpan;

class SortUseCase
{
    #[WithSpan]
    public function doSort(
        #[SpanAttribute] array $array,
    )
    {
        return sort($array);
    }
}