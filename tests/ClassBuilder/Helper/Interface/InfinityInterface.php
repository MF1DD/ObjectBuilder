<?php
declare(strict_types=1);

namespace Timelesstron\ObjectBuilder\Tests\ClassBuilder\Helper\Interface;

interface InfinityInterface
{
    public function get(): InfinityInterface;
}
