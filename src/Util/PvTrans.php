<?php

namespace App\Util;

class PvTrans
{

    static public function trans($object, string $field, string $locale = 'de')
    {
        return self::translate($object, $field, $locale);
    }

    static public function translate($object, string $field, string $locale = 'de')
    {

        if(is_object($object)) {
            $object = [
                $field => $object->{'get'.ucfirst($field)}(),
                'translations' => $object->getTranslations(),
            ];
        }

        $result = $object[$field];

        if(!$locale) {
            $locale = 'de';
        }

        if(!isset($object['translations']) || !is_array($object['translations'])) {
            return $result;
        }

        if($locale === 'de') {
            if($result) {
                return $result;
            }
            if(isset($object['translations']['fr'][$field]) && $object['translations']['fr'][$field]) {
                return $object['translations']['fr'][$field];
            }
            if(isset($object['translations']['fr']) && $object['translations']['fr'] && is_string($object['translations']['fr'])) {
                return $object['translations']['fr'];
            }
            if(isset($object['translations']['it'][$field]) && $object['translations']['it'][$field]) {
                return $object['translations']['it'][$field];
            }
            if(isset($object['translations']['it']) && $object['translations']['it'] && is_string($object['translations']['it'])) {
                return $object['translations']['it'];
            }

            return $result;
        }

        if($locale === 'fr') {
            if(isset($object['translations']['fr'][$field]) && $object['translations']['fr'][$field]) {
                return $object['translations']['fr'][$field];
            }
            if(isset($object['translations']['fr']) && $object['translations']['fr'] && is_string($object['translations']['fr'])) {
                return $object['translations']['fr'];
            }
            if($result) {
                return $result;
            }
            if(isset($object['translations']['it'][$field]) && $object['translations']['it'][$field]) {
                return $object['translations']['it'][$field];
            }
            if(isset($object['translations']['it']) && $object['translations']['it'] && is_string($object['translations']['it'])) {
                return $object['translations']['it'];
            }

            return $result;
        }

        if($locale === 'it') {
            if(isset($object['translations']['it'][$field]) && $object['translations']['it'][$field]) {
                return $object['translations']['it'][$field];
            }
            if(isset($object['translations']['it']) && $object['translations']['it'] && is_string($object['translations']['it'])) {
                return $object['translations']['it'];
            }
            if(isset($object['translations']['fr'][$field]) && $object['translations']['fr'][$field]) {
                return $object['translations']['fr'][$field];
            }
            if(isset($object['translations']['fr']) && $object['translations']['fr'] && is_string($object['translations']['fr'])) {
                return $object['translations']['fr'];
            }
            if($result) {
                return $result;
            }

            return $result;
        }

        return null;

    }

}