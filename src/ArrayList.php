<?php

namespace PureLib\Collection;

/**
 * 为数组添加常用方法
 *
 */
class ArrayList extends \ArrayObject {
    /**
     * 实例化当前类对象
     * @param array $arr
     * @return \PureLib\Collection\ArrayList
     */
    public static function newInstance($arr) {
        return new self ( $arr );
    }
    
    /**
     * 对数据进行MAP操作，类似array_map函数，
     * @param callable $callback callback 接受两个参数，第一个参数为当前数组项的KEY值，第二个参数是当前数组项的值
     * @return \PureLib\Collection\ArrayList
     */
    public function map($callback) {
        $result = new self ();
        foreach ( $this as $k => $v ) {
            $result [$k] = $callback ( $k, $v );
        }
        return $result;
    }
    
    /**
     * 对数据进行filter操作，类似array_filter函数，第一个参数为当前数组项的KEY值，第二个参数是当前数组项的值
     * @param callback $callback
     * @return Ambigous <\PureLib\Collection\ArrayList, null>
     */
    public function filter($callback) {
        $result = new self ();
        foreach ( $this as $k => $v ) {
            if ($callback ( $k, $v )) {
                $result [$k] = $v;
            }
        }
        return $result;
    }
    
    /**
     * 对数据循环执行callback,第一个参数为当前数组项的KEY值，第二个参数是当前数组项的值
     * @param unknown $callback
     * @return void
     */
    public function each($callback) {
        foreach ( $this as $k => $v ) {
            $callback ( $k, $v );
        }
    }
}