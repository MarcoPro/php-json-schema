<?php

namespace Swaggest\JsonSchema\Structure;

use Swaggest\JsonSchema\Constraint\Properties;
use Swaggest\JsonSchema\Context;
use Swaggest\JsonSchema\NameMirror;
use Swaggest\JsonSchema\Schema;

trait ClassStructureTrait
{
    use ObjectItemTrait;

    /**
     * @return ClassSchema
     */
    public static function schema()
    {
        static $schemas = array();
        $className = get_called_class();
        $schema = &$schemas[$className];

        if (null === $schema) {
            $schema = new ClassSchema();
            $properties = new Properties();
            $schema->properties = $properties;
            $schema->objectItemClass = $className;
            static::setUpProperties($properties, $schema);
        }

        return $schema;
    }

    /**
     * @return Properties|static
     */
    public static function properties()
    {
        return static::schema()->properties;
    }

    /**
     * @param $data
     * @param \Swaggest\JsonSchema\Context $options
     * @return static
     */
    public static function import($data, Context $options = null)
    {
        return static::schema()->in($data, $options);
    }

    /**
     * @param $data
     * @param Context $options
     * @return mixed
     * @throws \Swaggest\JsonSchema\InvalidValue
     */
    public static function export($data, Context $options = null)
    {
        return static::schema()->out($data, $options);
    }

    /**
     * @param ObjectItemContract $objectItem
     * @return static
     */
    public static function pick(ObjectItemContract $objectItem)
    {
        $className = get_called_class();
        return $objectItem->getNestedObject($className);
    }

    /**
     * @return static
     */
    public static function create()
    {
        return new static;
    }

    protected $__validateOnSet = true; // todo skip validation during import

    public function jsonSerialize()
    {
        $result = new \stdClass();
        $properties = static::schema()->properties;
        foreach ($properties->toArray() as $name => $schema) {
            $value = $this->$name;
            if ((null !== $value) || array_key_exists($name, $this->__arrayOfData)) {
                $result->$name = $value;
            }
        }
        foreach ($properties->nestedPropertyNames as $name) {
            /** @var ObjectItem $nested */
            $nested = $this->$name;
            if (null !== $nested) {
                foreach ((array)$nested->jsonSerialize() as $key => $value) {
                    $result->$key = $value;
                }
            }
        }

        return $result;
    }

    /**
     * @return static
     */
    public static function names()
    {
        static $nameflector = null;
        if (null === $nameflector) {
            $nameflector = new NameMirror();
        }
        return $nameflector;
    }

    public function __set($name, $column) // todo nested schemas
    {
        if ($this->__validateOnSet) {
            if ($property = static::schema()->properties[$name]) {
                $property->out($column);
            }
        }
        $this->__arrayOfData[$name] = $column;
        return $this;
    }

    public static function className()
    {
        return get_called_class();
    }

    public function validate()
    {
        static::schema()->out($this);
    }
}