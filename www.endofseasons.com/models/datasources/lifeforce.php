<?php


class Lifeforce extends AppModel {
    var $name = 'Lifeforce';
    //var $cacheQueries = true;
    public static $LifeforcesByName = array( );
    public static $LifeforcesByID = array( );
    
    function __construct( ) {
        parent::__construct();
        $this->init();
    }
    
    public function grab( $type='' ) {
        $this->init();
        if( is_numeric( $type ) ) {
            if( array_key_exists( $type, Lifeforce::$LifeforcesByID ) ) {
                return( Lifeforce::$LifeforcesByID[$type] );
            }
        } else {
            if( array_key_exists( $type, Lifeforce::$LifeforcesByName ) ) {
                return( Lifeforce::$LifeforcesByName[$type] );
            }
        }
        
        return( array() );
    }

        
    
    
    private function init( ) {
        if(! count( Lifeforce::$LifeforcesByName ) ) {
           $this->load();
        }
    }
    
    private function load() {
        $comps = Set::extract( $this->find( 'all' ), '{n}.Lifeforce' );
        foreach( $comps as $c ) {
            Lifeforce::$LifeforcesByName[$c['name']] = $c;
            Lifeforce::$LifeforcesByID[$c['id']] = $c;
        }
    }
}

?>