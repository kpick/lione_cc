<?php


class Sku extends AppModel {
    var $name = 'Sku';
    //var $cacheQueries = true;
    public static $SkuByName = array( );
    public static $SkuByID = array( );

    
    
    function __construct( ) {
        parent::__construct();
        $this->init();
    }
    
    public function grab( $type='' ) {
        $this->init();
        if( is_numeric( $type ) ) {
            if( array_key_exists( $type, Sku::$SkuByID ) ) {
                return( Sku::$SkuByID[$type] );
            }
        } elseif( $type ) {
            if( array_key_exists( $type, Sku::$SkuByName) ) {
                return( Sku::$SkuByName[$type] );
            }
        } else {
            return( Sku::$SkuByName);
        }
    }

        
    
    
    private function init( ) {
        if(! count( Sku::$SkuByName ) ) {
           $this->load();
        }
    }
    
    private function load() {
        $skus = Set::extract( $this->find( 'all' ), '/Sku/.' );
        foreach( $skus as $sku ) {
            Sku::$SkuByName[$sku['value']] = $sku;
            Sku::$SkuByID[$sku['id']] = $sku;
        }
    }
}

?>