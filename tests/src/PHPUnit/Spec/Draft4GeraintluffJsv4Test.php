<?php

namespace Swaggest\JsonSchema\Tests\PHPUnit\Spec;


use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class Draft4GeraintluffJsv4Test extends Draft4Test
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
     * @param $version
     * @throws \Exception
     */
    protected function runSpecTest($schemaData, $data, $isValid, $version)
    {
        $error = '';

        $actualValid = \Jsv4::isValid($data, $schemaData);

        $this->assertSame($isValid, $actualValid, "Schema:\n" . json_encode($schemaData, JSON_PRETTY_PRINT)
            . "\nData:\n" . json_encode($data, JSON_PRETTY_PRINT)
            . "\nError: " . $error . "\n");

    }
}