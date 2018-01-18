<?php

namespace Swaggest\JsonSchema\Tests\PHPUnit\Spec;

use Opis\JsonSchema\Loaders\Memory;
use Opis\JsonSchema\Validator;

class Draft6OpisTest extends Draft6Test
{
    private static function getLoader()
    {
        static $schemaStorage;

        if ($schemaStorage === null) {
            $schemaStorage = new Memory();

            $schemaStorage->add(
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/json-schema-draft6.json')),
                'http://json-schema.org/draft-06/schema');
            $schemaStorage->add(
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/json-schema-draft7.json')),
                'http://json-schema.org/draft-07/schema');

            $schemaStorage->add(
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/integer.json')),
                'http://localhost:1234/integer.json');
            $schemaStorage->add(
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/subSchemas.json')),
                'http://localhost:1234/subSchemas.json');
            $schemaStorage->add(
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/name.json')),
                'http://localhost:1234/name.json');
            $schemaStorage->add(
                json_decode(file_get_contents(__DIR__
                    . '/../../../../spec/JSON-Schema-Test-Suite/remotes/folder/folderInteger.json')),
                'http://localhost:1234/folder/folderInteger.json');
        }

        return $schemaStorage;
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
        $validator = new Validator(null, self::getLoader());

        $result = $validator->dataValidation($data, $schemaData);

        $actualValid = $result->isValid();

        $this->assertSame($isValid, $actualValid, "Schema:\n" . json_encode($schemaData, JSON_PRETTY_PRINT)
            . "\nData:\n" . json_encode($data, JSON_PRETTY_PRINT)
            . "\nError: " . $error . "\n");

    }
}