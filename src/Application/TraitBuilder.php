<?php

declare(strict_types=1);

namespace MF1DD\Application;

use ReflectionClass;
use RuntimeException;
use MF1DD\Domain\ClassBuilderInterface;

class TraitBuilder implements ClassBuilderInterface
{
    /**
     * @param ReflectionClass<object> $class
     * @param array<string, mixed> $parameters
     */
    public function build(ReflectionClass $class, array $parameters, array $constraints = []): object
    {
        $traitName = $class->getName();
        $code = sprintf("<?php\nreturn new class { use %s; };", $traitName);

        $tmpFile = tempnam(sys_get_temp_dir(), 'obuild_trait_');
        if ($tmpFile === false) {
            throw new RuntimeException('Failed to create temporary file for trait builder.');
        }

        file_put_contents($tmpFile, $code);

        try {
            $result = include $tmpFile;
        } finally {
            unlink($tmpFile);
        }

        return $result;
    }
}
