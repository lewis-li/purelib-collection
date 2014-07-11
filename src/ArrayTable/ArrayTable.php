<?php

namespace PureLib\Collection\ArrayTable;

/**
 * 为符合条件的二维或多维数组提供常用的方法.
 * 
 * @todo 优化:读取关联字段时,才触发关联数组的分析和合并
 * @todo 考虑使用AppendIterator的可行性
 */
class ArrayTable {
    protected $schema = array ();
    protected $data;
    protected $relations = array ();
    
    /**
     * @param array $data 数组
     * @param array $schema 数组定义（pk: 主键）
     */
    public function __construct(array $data = null, array $schema = null) {
        $this->data = $data;
        $this->schema = $schema;
    }
    
    /**
     * 新建当前类对象
     * @param string $data
     * @param array $schema
     * @return \PureLib\Collection\ArrayTable\ArrayTable
     */
    public static function newInstance($data=null, array $schema = null) {
        return new self($data, $schema);
    }
    
    /**
     * 设置数组主键
     * @param string $key
     * @return Ambigous <boolean, multitype:>|\PureLib\Collection\ArrayTable\ArrayTable
     */
    public function pk($key = null) {
        if ($key === null) {
            return isset ( $this->schema ['pk'] ) ? $this->schema ['pk'] : false;
        } else {
            $this->schema ['pk'] = $key;
            return $this;
        }
    }
    
    /**
     * 添加数组关联
     * @param array $options
     * -# name 关联名称，该名称在数组合并时，作为返回数组的一个字段
     * -# fk 关联字段
     * -# data 数据
     * @throws \Exception
     * @return \PureLib\Collection\ArrayTable\ArrayTable
     */
    public function relate(array $options) {
        if (! isset ( $options ['name'] ) && ! is_null ( $options ['name'] ) || ! isset ( $options ['fk'] ) && ! is_null ( $options ) || ! isset ( $options ['data'] ) && ! is_null ( $options ['data'] )) {
            throw new \Exception ( '缺少必要选项' );
        }
        
        $this->relations [$options ['name']] = $options;
        return $this;
    }
    
    /**
     *
     * @param array $options
     *            -# callable filter 对每一行数据进行filter和map操作，返回false时，过滤当前行.接受一个参数,代表数据的当前行
     *            -# bool relation 是否合并关联数组
     * @throws \Exception
     * @return Ambigous <multitype:, string>
     */
    public function toArray(array $options = null) {
        if (isset ( $options ['relation'] ) && $options ['relation'] === true && ! empty ( $this->relations )) {
            $data = $this->getMasterAndRelationData ();
        } else {
            $data = $this->data;
        }
        
        if (! empty ( $data ) && isset ( $options ['filter'] ) && is_callable ( $options ['filter'] )) {
            $filter = $options ['filter'];
            $data = array_filter ( array_map ( $filter, $data ) );
        }
        
        return $data;
    }
    /**
     * 将MASTER和其他关联数据进行合并
     * @throws \Exception
     * @return Ambigous <NULL, string>
     */
    protected function getMasterAndRelationData() {
        if (! isset ( $this->schema ['pk'] )) {
            throw new \Exception ( '必须指定主键' );
        }
        
        $data = $this->data;
        // $relation_names = array_keys ( $this->relations );
        $relation_names = array ();
        $emptydata_relation = array ();
        $count = count ( $data );
        $pk = $this->schema ['pk'];
        
        foreach ( $this->relations as $map_name => $relation ) {
            
            $map = array ();
            if (is_callable ( $relation ['data'] )) {
                $relation_data = $this->relations [$map_name] ['data'] = $relation ['data'] ();
            } else {
                $relation_data = $relation ['data'];
            }
            ;
            
            if (empty ( $relation_data )) {
                $emptydata_relation [] = $map_name;
                continue;
            }
            
            $fk = $relation ['fk'];
            
            if (is_array ( $fk )) { // 联合外键
                foreach ( $relation_data as $index => $row ) {
                    $_ = array ();
                    foreach ( $fk as $f ) {
                        $_ [$f] = $row [$f];
                    }
                    $map [$index] = $_;
                }
            } 

            else { // 单一外键
                foreach ( $relation_data as $index => $row ) {
                    $map [$index] = $row [$fk];
                }
            }
            
            $this->relations [$map_name] ['map'] = $map;
            $relation_names [] = $map_name;
        }
        
        if (is_array ( $pk )) { // 联合主键
            for($i = 0; $i < $count; $i ++) {
                foreach ( $relation_names as $name ) {
                    $_ = array ();
                    foreach ( $pk as $k ) {
                        $_ [$k] = $data [$i] [$k];
                    }
                    if (($n = array_search ( $_, $this->relations [$name] ['map'] )) !== false) {
                        $data [$i] [$name] = $this->relations [$name] ['data'] [$n];
                    } else {
                        $data [$i] [$name] = null;
                    }
                }
                
                foreach ( $emptydata_relation as $name ) {
                    $data [$i] [$name] = null;
                }
            }
        } 

        else { // 普通主键
            for($i = 0; $i < $count; $i ++) {
                foreach ( $relation_names as $name ) {
                    if (($n = array_search ( $data [$i] [$pk], $this->relations [$name] ['map'] )) !== false) {
                        $data [$i] [$name] = $this->relations [$name] ['data'] [$n];
                    } else {
                        $data [$i] [$name] = null;
                    }
                }
                
                foreach ( $emptydata_relation as $name ) {
                    $data [$i] [$name] = null;
                }
            }
        }
        
        return $data;
    }
}