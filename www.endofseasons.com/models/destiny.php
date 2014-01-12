<?php


class Destiny extends AppModel {
    var $name = 'Destiny';
    //var $cacheQueries = true;
    public static $DestiniesByName = array( );
    public static $DestiniesByID = array( );
    
    function __construct( ) {
        parent::__construct();
        $this->init();
    }
    
    public function grab( $type='' ) {
        $this->init();
        if( is_numeric( $type ) ) {
            if( array_key_exists( $type, Destiny::$DestiniesByID ) ) {
                return( Destiny::$DestiniesByID[$type] );
            }
        } else {
            if( array_key_exists( $type, Destiny::$DestiniesByName ) ) {
                return( Destiny::$DestiniesByName[$type] );
            }
        }
        
        return( array() );
    }

        
    
    
    private function init( ) {
        if(! count( Destiny::$DestiniesByName ) ) {
           $this->load();
        }
    }
    
    private function load() {
        $comps = Set::extract( $this->find( 'all' ), '{n}.Destiny' );
        foreach( $comps as $c ) {
            Destiny::$DestiniesByName[$c['name']] = $c;
            Destiny::$DestiniesByID[$c['id']] = $c;
        }
    }
}

?>