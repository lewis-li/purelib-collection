<?php
namespace PureLib\Collection;

class ArrayList extends \ArrayObject {
    public static function newInstance($arr) {
        return new self($arr);
    }
    
    public function map($callback) {
        $result = new self();
        foreach ($this as $k=>$v) {
            $result[$k] = $callback($k, $v);
        }
        return $result;
    }
    
    public function filter($callback) {
        $result = new self();
        foreach ($this as $k=>$v) {
            if ($callback($k, $v)) {
                $result[$k] = $v;
            }
        }
        return $result;
    }
}