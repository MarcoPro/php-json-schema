<?php

namespace Swaggest\JsonSchema\Constraint;

class Format
{
    const DATE_REGEX_PART = '(\d{4})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])';
    const TIME_REGEX_PART = '([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\.[0-9]+)?(Z|(\+|-)([01][0-9]|2[0-3]):([0-5][0-9]))';
    const HOSTNAME_REGEX = '/^(([a-z0-9]|[a-z0-9][a-z0-9\-]*[a-z0-9]){1,63}\.)*([a-z0-9]|[a-z0-9][a-z0-9\-]*[a-z0-9]){1,63}$/i';
    const JSON_POINTER_REGEX = '_^(?:/|(?:/[^/#]*)*)$_';
    const JSON_POINTER_RELATIVE_REGEX = '~^(0|[1-9][0-9]*)((?:/[^/#]+)*)(#?)$~';
    const JSON_POINTER_UNESCAPED_TILDE = '/~([^01]|$)/';

    const IS_URI_REFERENCE = 1;
    const IS_URI_TEMPLATE = 2;

    public static function validationError($format, $data)
    {
        switch ($format) {
            case 'date-time':
                return self::dateTimeError($data);
            case 'date':
                return preg_match('/^' . self::DATE_REGEX_PART . '/i', $data) ? null : 'Invalid date';
            case 'time':
                return preg_match('/^' . self::TIME_REGEX_PART . '/i', $data) ? null : 'Invalid time';
            case 'uri':
                return self::uriError($data);
            case 'email':
                return filter_var($data, FILTER_VALIDATE_EMAIL) ? null : 'Invalid email';
            case 'idn-email':
                return count(explode('@', $data, 3)) === 2 ? null : 'Invalid email';
            case 'ipv4':
                return filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? null : 'Invalid ipv4';
            case 'ipv6':
                return filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? null : 'Invalid ipv6';
            case 'hostname':
                return preg_match(self::HOSTNAME_REGEX, $data) ? null : 'Invalid hostname';
            case 'idn-hostname':
                return ($data = self::idn($data)) && preg_match(self::HOSTNAME_REGEX, $data)
                    ? null : 'Invalid hostname';
            case 'regex':
                return self::regexError($data);
            case 'json-pointer':
                return self::jsonPointerError($data);
            case 'relative-json-pointer':
                return self::jsonPointerError($data, true);
            case 'uri-reference':
                return self::uriError($data, self::IS_URI_REFERENCE);
            case 'uri-template':
                return self::uriError($data, self::IS_URI_TEMPLATE);
        }
        return null;
    }

    public static function idn($data)
    {
        static $functionExists = null;
        if ($functionExists === null) {
            $functionExists = function_exists('idn_to_ascii');
        }
        if ($functionExists) {
            $data = idn_to_ascii($data, 0, INTL_IDNA_VARIANT_UTS46);
        }
        return $data;
    }

    public static function dateTimeError($data)
    {
        return preg_match('/^' . self::DATE_REGEX_PART . 'T' . self::TIME_REGEX_PART . '$/i', $data)
            ? null
            : 'Invalid date-time';
    }

    public static function uriError($data, $options = 0)
    {
        if ($options === self::IS_URI_TEMPLATE) {
            $opened = false;
            for ($i = 0; $i < strlen($data); ++$i) {
                if ($data[$i] === '{') {
                    if ($opened) {
                        return 'Invalid uri-template: unexpected "{"';
                    } else {
                        $opened = true;
                    }
                } elseif ($data[$i] === '}') {
                    if ($opened) {
                        $opened = false;
                    } else {
                        return 'Invalid uri-template: unexpected "}"';
                    }
                }
            }
            if ($opened) {
                return 'Invalid uri-template: unexpected end of string';
            }
        }

        $uri = parse_url($data);
        if (!$uri) {
            return 'Malformed URI';
        }
        if (0 === $options && (!isset($uri['scheme']) || $uri['scheme'] === '')) {
            return 'Missing scheme in URI';
        }
        if (isset($uri['host'])) {
            if (!preg_match(self::HOSTNAME_REGEX, $uri['host'])) {
                $host = $uri['host'];
                // stripping [ ]
                if ($host[0] === '[' && $host[strlen($host) - 1] === ']') {
                    $host = substr($host, 1, -1);
                }
                if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    return 'Malformed host in URI';
                }
            }
        }

        if (isset($uri['path'])) {
            if (strpos($uri['path'], '\\') !== false) {
                return 'Invalid path: unescaped backslash';
            }
        }

        if (isset($uri['fragment'])) {
            if (strpos($uri['fragment'], '\\') !== false) {
                return 'Invalid fragment: unescaped backslash';
            }
        }

        return null;
    }

    public static function regexError($data)
    {
        if (substr($data, -2) === '\Z') {
            return 'Invalid regex: \Z is not supported';
        }
        if (substr($data, 0, 2) === '\A') {
            return 'Invalid regex: \A is not supported';
        }

        return @preg_match('/' . $data . '/', '') === false ? 'Invalid regex' : null;
    }

    public static function jsonPointerError($data, $isRelative = false)
    {
        if (preg_match(self::JSON_POINTER_UNESCAPED_TILDE, $data)) {
            return 'Invalid json-pointer: unescaped ~';
        }
        if ($isRelative) {
            return preg_match(self::JSON_POINTER_RELATIVE_REGEX, $data) ? null : 'Invalid relative json-pointer';
        } else {
            return preg_match(self::JSON_POINTER_REGEX, $data) ? null : 'Invalid json-pointer';
        }
    }
}