<?php

declare(strict_types=1);

namespace vlaim\PioCheck\dto;

/**
 * @psalm-suppress RawObjectIteration
 * @psalm-suppress MixedAssignment
 */
class Dto
{
    public function __construct(\stdClass $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

}