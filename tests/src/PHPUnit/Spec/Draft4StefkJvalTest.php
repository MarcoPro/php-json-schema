<?php

namespace Swaggest\JsonSchema\Tests\PHPUnit\Spec;


use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class Draft4StefkJvalTest extends Draft4Test
{
    private static function getPrefetchHook()
    {
        static $hook;

        if ($hook === null) {

            $hook = function ($uri) {
                return str_replace(
                    'http://localhost:1234',
                    'file://' . __DIR__ . '/../../../../spec/JSON-Schema-Test-Suite/remotes',
                    $uri
                );
            };
        }

        return $hook;
    }

    /**
     * @param $schemaData
     * @param $data
     * @param $isValid
     * @param $name
     * @param $version
     * @throws \Exception
     */
    protected function runSpecTest($schemaData, $data, $isValid, $name, $version)
    {
        $error = '';

        $validator = \JVal\Validator::buildDefault(self::getPrefetchHook());
        $violations = $validator->validate($data, $schemaData);
        $actualValid = empty($violations);

        $this->assertSame($isValid, $actualValid,
            "Test: $name\n"
            . "Schema:\n" . json_encode($schemaData, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES)
            . "\nData:\n" . json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES)
            . "\nError: " . $error . "\n");

    }
}