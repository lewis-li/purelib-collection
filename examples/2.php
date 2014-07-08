<?php
use \PureLib\Collection\ArrayTable\ArrayTable as ArrayTable;

require __DIR__ . '/../src/ArrayTable/ArrayTable.php';

$master = array (
        array (
                'id' => 1,
                'rate_id' => 2,
                'stamp' => 2,
                'price' => '2',
                
                'other' => '100' 
        ),
        array (
                'id' => 3,
                'rate_id' => 3,
                'stamp' => 3,
                'price' => '3',
                'other' => '99' 
        ),
        array (
                'id' => 4,
                'rate_id' => 4,
                'stamp' => 4,
                'price' => '3',
                'other' => '99' 
        ) 
);

$table = new ArrayTable ( $master, array (
        'pk' => array('rate_id','stamp'), 
) );
// $table->pk('id');

$table->relate ( array (
        'name' => 'breakfast',
        'fk' => array('rate_id', 'stamp'),
        'data' => function () {
            return array (
                    array (
                            'id' => 1,
                            'rate_id' => 3,
                            'stamp' => 3,
                            'breakfast' => '2',
                            'other' => 'm1' 
                    ),
                    array (
                            'id' => 2,
                            'rate_id' => 4,
                            'stamp' => 4,
                            'breakfast' => '2',
                            'other' => 'm1' 
                    ) 
            );
        } 
) );

/* $table->relate ( array (
        'name' => 'occupancy',
        'fk' => 'id',
        'data' => function () {
            return null;
        } 
) ); */

$result = $table->toArray ( array (
        'relation' => true,
        'filter' => function ($row) {
            if ($row ['price'] > 2 && $row ['other'] < 101) {
                return array (
                        'rate_id' => $row['rate_id'],
                        'stamp' => $row['stamp'],
                        'breakfast' => $row ['breakfast'] ['breakfast'],
                        //'id' => $row ['id'],
                        'price' => $row ['price'],
                        
                        
                        //'occupancy' => $row ['occupancy'] ['occupancy'] 
                );
            }
        } 
) );

var_dump ( $result );

?>
