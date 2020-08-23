<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes;

use InvalidArgumentException;
use Stillat\Meerkat\Core\Data\Types;
use Stillat\Meerkat\Core\Data\Validators\BitValidator;
use Stillat\Meerkat\Core\Data\Validators\IntegerValidator;
use Stillat\Meerkat\Core\Data\Validators\StringValidator;

/**
 * Class PrototypeAttributeValidator
 *
 * Provides utilities for validating internal comment prototype attributes.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes
 * @since 2.0.0
 */
class PrototypeAttributeValidator
{

    /**
     * Validates the supplied attributes against their expected runtime types.
     *
     * @param array $attributes The attributes to validate.
     * @throws InvalidArgumentException
     */
    public static function validateAttributes($attributes)
    {
        $internalAttributes = PrototypeAttributes::getPrototypeExpectedTypes();

        foreach ($attributes as $attributeName => $attributeValue) {
            if (array_key_exists($attributeName, $internalAttributes)) {
                $type = intval($internalAttributes[$attributeName]);

                if ($type === Types::TYPE_STRING && StringValidator::check($attributeValue) === false) {
                    throw new InvalidArgumentException(self::getErrorMessage(
                        $attributeName, Types::TYPE_STRING, $attributeValue
                    ));
                }

                if ($type === Types::TYPE_BIT && BitValidator::check($attributeValue) === false) {
                    throw new InvalidArgumentException(self::getErrorMessage(
                        $attributeName, Types::TYPE_BIT, $attributeValue
                    ));
                }

                if ($type === Types::TYPE_INTEGER && IntegerValidator::check($attributeValue) === false) {
                    throw new InvalidArgumentException(self::getErrorMessage(
                        $attributeName, Types::TYPE_INTEGER, $attributeValue
                    ));
                }
            }
        }
    }

    /**
     * Generates an exception message for invalid types.
     *
     * @param string $attributeName The attribute name.
     * @param int $expectedType The expected type.
     * @param mixed $value The value provided.
     * @return string
     */
    private static function getErrorMessage($attributeName, $expectedType, $value)
    {
        if ($expectedType === Types::TYPE_INTEGER) {
            $expectedType = 'integer';
        } elseif ($expectedType === Types::TYPE_BIT) {
            $expectedType = 'boolean';
        } elseif ($expectedType === Types::TYPE_STRING) {
            $expectedType = 'string';
        }

        return $attributeName . ' expects ' . $expectedType . ' type. ' . gettype($value) . ' provided.';
    }

}
