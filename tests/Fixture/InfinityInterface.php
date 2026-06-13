<?php
declare(strict_types=1);

namespace MF1DD\Tests\Fixture;

interface InfinityInterface
{
    public function get(): InfinityInterface;
}
