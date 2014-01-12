<?php


class Transaction extends AppModel {
    var $name = 'Transaction';
    var $belongsTo = array( 'Player' );
    var $hasAndBelongsToMany = array(
        'Skus' =>
            array(
                'className'              => 'Sku',
                'joinTable'              => 'transactions_skus',
                'foreignKey'             => 'transaction_id',
                'associationForeignKey'  => 'sku_id',
                'unique'                 => true,
                'conditions'             => '',
                'fields'                 => '',
                'order'                  => '',
                'limit'                  => '',
                'offset'                 => '',
                'finderQuery'            => '',
                'deleteQuery'            => '',
                'insertQuery'            => ''
            )
      );

}

?>