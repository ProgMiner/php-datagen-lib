<?php

/* MIT License

Copyright (c) 2018 Eridan Domoratskiy

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE. */

namespace PHPDataGen;

/**
 * A trait for data classes
 *
 * @author Eridan Domoratskiy <eridan200@mail.ru>
 */
trait DataClassTrait {

    protected static function getFields(): array {
        static $fields = null;

        if (is_null($fields)) {
            if ((new ReflectionClass(parent::class))->hasMethod('getFields')) {
                $fields = array_merge((array) parent::getFields(), self::FIELDS);
            } else {
                $fields = self::FIELDS;
            }
        }

        return $fields;
    }

    public function __isset($key) {
        if (!isset(self::getFields()[$key])) {
            return false;
        }

        if (isset(parent::getFields()[$key])) {
            return parent::__isset($key);
        }

        return isset($key);
    }

    public function &__get($key) {
        if (!isset(self::getFields()[$key])) {
            throw new \UnexpectedValueException("Variable \"{$key}\" is not exists");
        }

        $getter = 'get'.self::getFields()[$key];
        if (method_exists($this, 'set'.self::getFields()[$key])) {
            $ret = &$this->$getter();
        } else {
            $ret = $this->$getter();
        }

        return $ret;
    }

    public function __set($key, $value) {
        $this->__get($key);

        $setter = 'set'.self::getFields()[$key];
        if (!method_exists($this, $setter)) {
            throw new \UnexpectedValueException("Variable \"{$key}\" is not editable");
        }

        $this->$setter($value);
    }

    public function __unset($key) {
        throw new \LogicException('Variable unset is not supported');
    }
}
