<?php
namespace luoyy\WilddogSmsSdk;

use \ArrayAccess;
use \Exception;
use \StdClass;

/**
 * 数据
 * @Author:zishang520
 * @Email:zishang520@gmail.com
 * @HomePage:http://www.luoyy.com
 * @version: 1.0 beta
 */
class Access extends StdClass implements ArrayAccess
{
    public function offsetExists($offset)
    {
        if (!is_scalar($offset)) {
            throw new Exception("offset must be a scalar", 1);
        }
        return property_exists($this, $offset);
    }
    public function offsetGet($offset)
    {
        if (!is_scalar($offset)) {
            throw new Exception("offset must be a scalar", 1);
        }
        if (property_exists($this, $offset)) {
            return $this->{strval($offset)};
        }
        return null;
    }
    public function offsetSet($offset, $value)
    {
        if (!is_scalar($offset)) {
            throw new Exception("offset must be a scalar", 1);
        }
        $this->{strval($offset)} = $value;
    }
    public function offsetUnset($offset)
    {
        if (!is_scalar($offset)) {
            throw new Exception("offset must be a scalar", 1);
        }
        if (property_exists($this, $offset)) {
            unset($this->{strval($offset)});
        }
    }

    public function toArray()
    {
        $data = [];
        foreach ($this as $key => $value) {
            $data[$key] = $value;
        }
        return $data;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
