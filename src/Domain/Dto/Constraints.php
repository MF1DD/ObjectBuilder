<?php

declare(strict_types=1);

namespace MF1DD\Domain\Dto;

final class Constraints
{
    /**
     * @param array<string, mixed> $options Raw constraint options from the user
     */
    public function __construct(
        public readonly array $options = [],
    ) {
    }

    public function min(): ?int
    {
        return isset($this->options['min']) ? (int) $this->options['min'] : null;
    }

    public function max(): ?int
    {
        return isset($this->options['max']) ? (int) $this->options['max'] : null;
    }

    public function length(): ?int
    {
        return isset($this->options['length']) ? (int) $this->options['length'] : null;
    }

    public function format(): ?string
    {
        /** @var string|null $value */
        $value = $this->options['format'] ?? null;

        return $value;
    }

    public function minLength(): ?int
    {
        return isset($this->options['min_length']) ? (int) $this->options['min_length'] : null;
    }

    public function maxLength(): ?int
    {
        return isset($this->options['max_length']) ? (int) $this->options['max_length'] : null;
    }
}
