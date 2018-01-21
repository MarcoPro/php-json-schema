<?php

namespace Swaggest\JsonSchema\Constraint;

use Swaggest\JsonSchema\Context;
use Swaggest\JsonSchema\Exception\ContentException;

class Content
{
    const MEDIA_TYPE_APPLICATION_JSON = 'application/json';
    const ENCODING_BASE64 = 'base64';

    const BASE64_INVALID_REGEX = '_[^A-Za-z0-9+/=]+_';


    /**
     * @param Context $options
     * @param string|null $encoding
     * @param string|null $mediaType
     * @param string $data
     * @param bool $import
     * @return bool|mixed|string
     * @throws ContentException
     */
    public static function process(Context $options, $encoding, $mediaType, $data, $import = true)
    {
        if ($import) {
            if ($encoding !== null) {
                switch ($encoding) {
                    case self::ENCODING_BASE64:
                        if ($options->strictBase64Validation && preg_match(self::BASE64_INVALID_REGEX, $data)) {
                            throw new ContentException('Invalid base64 string');
                        }
                        $data = base64_decode($data);
                        if ($data === false) {
                            throw new ContentException('Unable to decode base64');
                        }
                        break;
                }
            }

            if ($mediaType !== null) {
                switch ($mediaType) {
                    case self::MEDIA_TYPE_APPLICATION_JSON:
                        $data = json_decode($data);
                        $lastError = json_last_error_msg();
                        if ($lastError !== 'No error') {
                            throw new ContentException('Unable to decode json: ' . $lastError);
                        }
                        break;

                }
            }

            return $data;
        } else {
            // export

            if ($mediaType !== null) {
                switch ($mediaType) {
                    case self::MEDIA_TYPE_APPLICATION_JSON:
                        $data = json_encode($data);
                        $lastError = json_last_error_msg();
                        if ($lastError !== 'No error') {
                            throw new ContentException('Unable to encode json: ' . $lastError);
                        }
                        break;
                }
            }

            if ($encoding !== null) {
                switch ($encoding) {
                    case self::ENCODING_BASE64:
                        $data = base64_encode($data);
                        break;

                }
            }

            return $data;
        }

    }
}