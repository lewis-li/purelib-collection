<?php

namespace PureLib\Collection\ArrayTable;

class ArrayTable {
    protected $schema = array ();
    protected $data;
    protected $relations = array ();
    public function __construct($data = null, array $schema = null) {
        $this->data = $data;
        $this->schema = $schema;
    }
    public function pk($key = null) {
        if ($key === null) {
            return isset ( $this->schema ['pk'] ) ? $this->schema ['pk'] : false;
        } else {
            $this->schema ['pk'] = $key;
            return $this;
        }
    }
    public function relate(array $options) {
        if (! isset ( $options ['name'] ) || ! isset ( $options ['fk'] ) || ! isset ( $options ['data'] )) {
            throw new \Exception ( '缺少必要选项' );
        }
        
        $this->relations [$options ['name']] = $options;
        return $this;
    }
    
    /**
     *
     * @param array $options
     *            -# callable filter
     *            -# bool relation
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
    
    protected function getMasterAndRelationData() {
        if (! isset ( $this->schema ['pk'] )) {
            throw new \Exception ( '必须指定主键' );
        }
        
        $data = $this->data;
        //$relation_names = array_keys ( $this->relations );
        $relation_names = array();
        $emptydata_relation = array();
        $count = count ( $data );
        $pk = $this->schema ['pk'];
        
        foreach ( $this->relations as $map_name => $relation ) {
            
            $map = array ();
            if (is_callable ( $relation ['data'] )) {
                $relation_data = $this->relations [$map_name] ['data'] = $relation ['data'] ();
            } else {
                $relation_data = $relation ['data'];
            };
            
            
            
            if (empty ( $relation_data )) {
                $emptydata_relation[] = $map_name;
                continue;
            }
            
            $fk = $relation ['fk'];
            
            if (is_array($fk)) { //联合外键
                foreach ( $relation_data as $index => $row ) {
                    $_ = array();
                    foreach ($fk as $f) {
                        $_[$f] = $row[$f];
                    }
                    $map [$index] = $_;
                }
            }
            
            else { //单一外键
                foreach ( $relation_data as $index => $row ) {
                    $map [$index] = $row [$fk];
                }
            }
            
            
            $this->relations [$map_name] ['map'] = $map;
            $relation_names[] = $map_name;
        }
        
        if (is_array($pk)) { //联合主键
            for($i = 0; $i < $count; $i ++) {
                foreach ( $relation_names as $name ) {
                    $_ = array();
                    foreach ($pk as $k) {
                        $_[$k] = $data[$i][$k];
                    }
                    if (($n = array_search ( $_, $this->relations [$name] ['map'] )) !== false) {
                        $data [$i] [$name] = $this->relations [$name] ['data'] [$n];
                    } else {
                        $data [$i] [$name] = null;
                    }
                }
            
                foreach ( $emptydata_relation as $name) {
                    $data[$i][$name] = null;
                }
            }
        }
        
        else { //普通主键
            for($i = 0; $i < $count; $i ++) {
                foreach ( $relation_names as $name ) {
                    if (($n = array_search ( $data [$i] [$pk], $this->relations [$name] ['map'] )) !== false) {
                        $data [$i] [$name] = $this->relations [$name] ['data'] [$n];
                    } else {
                        $data [$i] [$name] = null;
                    }
                }
            
                foreach ( $emptydata_relation as $name) {
                    $data[$i][$name] = null;
                }
            }
        }
        
        return $data;
    }
}