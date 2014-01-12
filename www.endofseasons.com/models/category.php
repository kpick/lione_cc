<?php


class Category extends AppModel {
    var $name = 'Category';
    //var $cacheQueries = true;
    public static $CategoriesByName = array( );
    public static $CategoriesByID = array( );
    
    
    function __construct( ) {
        parent::__construct();
        $this->init();
    }
    
    public function grab( $type='' ) {
        $this->init();
        if( is_numeric( $type ) ) {
            if( array_key_exists( $type, Category::$CategoriesByID ) ) {
                return( Category::$CategoriesByID[$type] );
            }
        } elseif( $type ) {
            if( array_key_exists( $type, Category::$CategoriesByName ) ) {
                return( Category::$CategoriesByName[$type] );
            }
        } else {
            return( Category::$CategoriesByName );
        }
    }

        
    
    
    private function init( ) {
        if(! count( Category::$CategoriesByName ) ) {
           $this->load();
        }
    }
    
    private function load() {
        $comps = Set::extract( $this->find( 'all' ), '{n}.Category' );
        foreach( $comps as $c ) {
            Category::$CategoriesByName[$c['name']] = $c;
            Category::$CategoriesByID[$c['id']] = $c;
        }
    }
}

?>