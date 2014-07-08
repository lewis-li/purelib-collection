<?php
use \PureLib\Collection\ArrayTable\ArrayTable as ArrayTable;

require __DIR__ . '/../src/ArrayTable/ArrayTable.php';

$master = array (
        array (
                'id' => 1,
                'price' => '2',
                'other' => '100' 
        ),
        array (
                'id' => 2,
                'price' => '3',
                'other' => '99' 
        ),
        array (
                'id' => 3,
                'price' => '3',
                'other' => '99' 
        ) 
);

$table = new ArrayTable ( $master, array (
        'pk' => 'id' 
) );
// $table->pk('id');

$table->relate ( array (
        'name' => 'breakfast',
        'fk' => 'id',
        'data' => function () {
            return array (
                    array (
                            'id' => 1,
                            'breakfast' => '2',
                            'other' => 'm1' 
                    ),
                    array (
                            'id' => 2,
                            'breakfast' => '2',
                            'other' => 'm1' 
                    ) 
            );
        } 
) );

$table->relate ( array (
        'name' => 'occupancy',
        'fk' => 'id',
        'data' => function () {
            return null;
        } 
) );

$result = $table->toArray ( array (
        'relation' => true,
        'filter' => function ($row) {
            if ($row ['price'] > 2 && $row ['other'] < 101) {
                return array (
                        'id' => $row ['id'],
                        'price' => $row ['price'],
                        'breakfast' => $row ['breakfast'] ['breakfast'],
                        'occupancy' => $row ['occupancy'] ['occupancy'] 
                );
            }
        } 
) );

var_dump ( $result );

?>
