<?php

namespace App;

class TranslateableMessage
{
    /**
     * Set placeholder and values for the TranslateableMessage.
     *
     * @param string     $placeholder
     * @param null|array $values
     */
    public static function get(String $placeholder, $values = null)
    {
        return [
            'placeholder' => $placeholder,
            'values'      => $values,
        ];
    }
}
