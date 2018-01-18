<?php

namespace Swaggest\JsonSchema\Tests\PHPUnit\Spec;


use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class Draft4JustinrainbowsTest extends Draft4Test
{
    private static function getFactory()
    {
        static $factory;

        if ($factory === null) {
            $schemaStorage = new SchemaStorage();
            $schemaStorage->addSchema(
                'http://localhost:1234/integer.json',
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/integer.json')));
            $schemaStorage->addSchema(
                'http://localhost:1234/subSchemas.json',
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/subSchemas.json')));
            $schemaStorage->addSchema(
                'http://localhost:1234/name.json',
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/name.json')));
            $schemaStorage->addSchema(
                'http://localhost:1234/folder/folderInteger.json',
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/folder/folderInteger.json')));
            $factory = new Factory($schemaStorage);
        }

        return $factory;
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
        echo $name, "\n";

        $error = '';
        $validator = new Validator(self::getFactory());
        $validator->validate($data, $schemaData);
        $actualValid = $validator->isValid();

        $this->assertSame($isValid, $actualValid, "Schema:\n" . json_encode($schemaData, JSON_PRETTY_PRINT)
            . "\nData:\n" . json_encode($data, JSON_PRETTY_PRINT)
            . "\nError: " . $error . "\n");

    }
}