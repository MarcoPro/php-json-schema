<?php

namespace Swaggest\JsonSchema\Tests\PHPUnit\Spec;

use Swaggest\JsonSchema\Context;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\RemoteRef\Preloaded;
use Swaggest\JsonSchema\Schema;

class Draft4Test extends \PHPUnit_Framework_TestCase
{
    const SCHEMA_VERSION = Schema::VERSION_DRAFT_04;

    protected $skipTests = array();
    protected $ignoreTests = array();

    public static function getProvider()
    {
        static $refProvider = null;

        if (null === $refProvider) {
            $refProvider = new Preloaded();
            $refProvider
                ->setSchemaData(
                    'http://localhost:1234/integer.json',
                    json_decode(file_get_contents(__DIR__
                        . '/../../../../spec/JSON-Schema-Test-Suite/remotes/integer.json')))
                ->setSchemaData(
                    'http://localhost:1234/subSchemas.json',
                    json_decode(file_get_contents(__DIR__
                        . '/../../../../spec/JSON-Schema-Test-Suite/remotes/subSchemas.json')))
                ->setSchemaData(
                    'http://localhost:1234/name.json',
                    json_decode(file_get_contents(__DIR__
                        . '/../../../../spec/JSON-Schema-Test-Suite/remotes/name.json')))
                ->setSchemaData(
                    'http://localhost:1234/folder/folderInteger.json',
                    json_decode(file_get_contents(__DIR__
                        . '/../../../../spec/JSON-Schema-Test-Suite/remotes/folder/folderInteger.json')));
        }

        return $refProvider;
    }

    /**
     * @dataProvider specProvider
     * @param $schemaData
     * @param $data
     * @param $isValid
     * @param $name
     * @throws \Exception
     */
    public function testSpec($schemaData, $data, $isValid, $name)
    {
        if (isset($this->skipTests[$name])) {
            $this->markTestSkipped();
            return;
        }
        $this->runSpecTest($schemaData, $data, $isValid, $name, static::SCHEMA_VERSION);
    }

    /**
     * @dataProvider specOptionalProvider
     * @param $schemaData
     * @param $data
     * @param $isValid
     * @param $name
     * @throws \Exception
     */
    public function testSpecOptional($schemaData, $data, $isValid, $name)
    {
        $this->runSpecTest($schemaData, $data, $isValid, $name, static::SCHEMA_VERSION);
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
        $refProvider = self::getProvider();

        $actualValid = true;
        $error = '';
        try {
            $options = new Context();
            $options->setRemoteRefProvider($refProvider);
            $options->version = $version;

            $schema = Schema::import($schemaData, $options);

            $res = $schema->in($data, $options);

            $exported = $schema->out($res);
            $this->assertEquals($data, $exported);
        } catch (InvalidValue $exception) {
            $actualValid = false;
            $error = $exception->getMessage();
        }

        $this->assertSame($isValid, $actualValid, "Schema:\n" . json_encode($schemaData, JSON_PRETTY_PRINT)
            . "\nData:\n" . json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES)
            . "\nError: " . $error . "\n");

    }


    /**
     * @dataProvider specProvider
     * @param $schemaData
     * @param $data
     * @param $isValid
     * @param $name
     */
    public function testSpecSkipValidation($schemaData, $data, $isValid, $name)
    {
        $this->runSpecTestSkipValidation($schemaData, $data, $isValid, $name);
    }

    private function runSpecTestSkipValidation($schemaData, $data, $isValid, $name)
    {
        $refProvider = self::getProvider();

        $actualValid = true;
        $error = '';
        try {
            $options = new Context();
            $options->setRemoteRefProvider($refProvider);
            $schema = Schema::import($schemaData, $options);
            $context = new Context();
            $context->skipValidation = true;
            $res = $schema->in($data, $context);

            $context = new Context();
            $context->skipValidation = true;
            $exported = $schema->out($res, $context);
            $this->assertEquals($data, $exported);
        } catch (InvalidValue $exception) {
            $actualValid = false;
            $error = $exception->getMessage();
        }


        $this->assertTrue($actualValid, "Schema:\n" . json_encode($schemaData, JSON_PRETTY_PRINT)
            . "\nData:\n" . json_encode($data, JSON_PRETTY_PRINT)
            . "\nError: " . $error . "\n");
    }

    public function specOptionalProvider()
    {
        $path = __DIR__ . '/../../../../spec/JSON-Schema-Test-Suite/tests/draft4/optional';
        return $this->provider($path);
    }

    public function specProvider()
    {
        $path = __DIR__ . '/../../../../spec/JSON-Schema-Test-Suite/tests/draft4';
        return $this->provider($path);
    }

    protected function provider($path)
    {
        if (!file_exists($path)) {
            //$this->markTestSkipped('No spec tests found, please run `git submodule bla-bla`');
        }

        $testCases = array();

        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if ('.json' !== substr($entry, -5)) {
                        continue;
                    }

                    //if ($entry !== 'refRemote.json') {
                    //continue;
                    //}

                    //echo "$entry\n";
                    /** @var _SpecTest[] $tests */
//                    $tests = json_decode(file_get_contents($path . '/' . $entry), false, 512, JSON_BIGINT_AS_STRING);
                    $tests = json_decode(file_get_contents($path . '/' . $entry));

                    foreach ($tests as $test) {
                        foreach ($test->tests as $case) {
                            /*if ($case->description !== 'changed scope ref invalid') {
                                continue;
                            }
                            */

                            $name = $entry . ' ' . $test->description . ': ' . $case->description;
                            $testCases[$name] = array(
                                'schema' => $test->schema,
                                'data' => $case->data,
                                'isValid' => $case->valid,
                                'name' => $name,
                            );
                        }
                    }
                }
            }
            closedir($handle);
        }

        //print_r($testCases);

        return $testCases;
    }


}

/**
 * @property $description
 * @property $schema
 * @property _SpecTestCase[] $tests
 */
class _SpecTest
{
}

/**
 * @property $description
 * @property $data
 * @property bool $valid
 */
class _SpecTestCase
{
}