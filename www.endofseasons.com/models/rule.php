<?php
class Rule extends AppModel {
    var $name = 'Rule';
    var $belongsTo = array( 'Category' );
    var $hasMany = array( 'Cost' );
    
    var $full_ruleset = array();
    
    //var $cacheQueries = true;
    var $rulesByID = array( );
    var $defaults = array();
    var $starting_destiny = '';
 


    // TODO: WITH CACHING CAN PROBABLY JUST REQUEST FROM DB
    // TODO: CAN THIS BE DONE WITH SET::?
    private function load($game_id) {

        $this->full_ruleset = $this->findAllByGameId( $game_id );

        
        // these are to make accessing certain portions
        // of the ruleset faster
        foreach( $this->full_ruleset as $data ) {
            $rule = $data['Rule'];
            $category = $data['Category'];
            $cost = $data['Cost'];
            $key =& $rule['id'];
            
            
            $this->rulesByID[$key] = $rule;
            $this->rulesByID[$key]['category'] = $category;
            $this->rulesByID[$key]['cost_modifiers'] = $cost;
            
            if( $rule['is_default_rule'] ) {
                $this->defaults[] = $rule['id'];
                if( $category['name'] == 'destiny' ) {
                    $this->starting_destiny = $rule['name'];
                }
                
                if( $category['name'] == 'lifeforce' ) {
                    $this->starting_lifeforce = $rule['name'];
                }
                
            }
            
        }
    }
    
    public function grab( $id ) {
        if(! array_key_exists( $id, $this->rulesByID) ) {
            return( array( ) );
        }
        return( $this->rulesByID[$id] );
    }
    
        
    public function loadDefaults( $character_stats = array( ) ) {
        if(! empty( $characters_stats ) ) {
            return( array_merge( $this->defaults, $character_stats ) );
        } else {
            return( $this->defaults );
        }
    }
    
    public function getStartingDestiny( ) {
        return( $this->starting_destiny );
    }

    public function getStartingLifeforce( ) {
        return( $this->starting_lifeforce );
    }    

    public function kickoff( $game_id ) {
        $this->load( $game_id );
    }

}
?>