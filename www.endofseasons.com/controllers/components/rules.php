<?php
class RulesComponent extends Object
{
    var $controller = true;
    var $filters      = array();
    var $originals    = array();
    var $skills_enabled      = array();
    var $tree       = array();
    var $mask       = array();
    var $skip       = array();
    var $selected   = array();
    var $character_info=array();
    var $character=0;
    var $cost_modifiers = array();
    
    var $modifiers  = array();
    public static $starting_destiny = '';
    public static $starting_lifeforce = '';
    var $level = 1;

    function startup(&$controller) {
        // This method takes a reference to the controller which is loading it.
        // Perform controller initialization here.
        $this->controller = &$controller;
        $this->rule_list  = &$controller->Rule->rulesByID;
    }
    
    private function resetTree( ) {
        $this->tree = array();
        $this->mask = array();
        $this->filters = array();
    }


     /*
    **  callback that replaces a 1 or a 0 into the prereq string
    */
    private function checkFilters( $matches) {
        if(! $this->check_against_callback ) {
            $this->check_against_callback = $this->filters;
        }
    	return in_array($matches[0],$this->check_against_callback) ? 1 : 0;
    }
    
    /** generates the skill tree for a controller method.  Populates the arrays: 
    	$tree (all of the skills available), $filters (the filter to pass into the tree),
    	$selected (the skills the char. has selected), and $enabled (the skills that are able to
    	be bought)
   	**/
    private function generateTree( $filters=array() ) {
        reset( $this->rule_list );
        $rerun=FALSE;
        
       /**  if filters are passed in, we can assume this is the first call.  Add the filters
         	to the object
        **/
        if( count( $filters ) ) {
            $this->filters = $filters;
            $this->selected = $filters;
            $this->originals = $filters;
            
            /** if any of the ids in the filter are part of a unique category, add the category to
                the skip array
            **/
            foreach( $this->filters as $id ) {
                $rule = $this->rule_list[$id];
                if( $rule['category']['is_unique'] ) {
                    $this->skip[] = $rule['category_id'];
                }
            }
        }
        
        foreach( $this->rule_list as $id=>$info ) {
            $category_id = $info['category_id'];
            $category_name = $info['category']['name'];

 			
 			/** don't show any whose category is in the mask **/
            if( in_array( $category_name, $this->mask ) ) continue;
            
            /** if the category branch isn't in the tree, create it **/
            if(! array_key_exists( $category_name, $this->tree ) ) $this->tree[$category_name]=array();
            
            /** skip any rules that are already in the tree **/
            if( in_array( $id, $this->tree[$category_name] ) ) continue;
            
            /** if we should skip this category and the id isn't in the filters **/
            if( in_array( $category_id, $this->skip ) && ! in_array( $id, $this->filters ) ) continue;

			/** add a root level rule (like "game") and continue **/
            if( $info['prereq'] == '*' ) {
                $this->filters[] = $id;
                $this->tree[$category_name][] = $id;
                continue;
            }

            /** check this rule against the current filters **/
            $this->check_against_callback = &$this->filters;
            $str1 = preg_replace_callback( '/\d+/', array( &$this, 'checkFilters'), $info['prereq'] ); 
            $this->check_against_callback = &$this->originals;           
            $str2 = preg_replace_callback( '/\d+/', array( &$this, 'checkFilters'), $info['prereq'] );            
            
            eval( "\$valid=$str1;" );
            if( $valid ) {
                $this->filters[] = $id;
                $this->tree[$category_name][] = $id;                
                $rerun=TRUE;
            }
             
             /** revalidate against originals to see if the skill should be enabled **/
            eval( "\$valid=$str2;" );
            if( $valid ) {
                $this->skills_enabled[]=$id;
                if( $info['is_essence'] ) {
                	$this->selected[] = $id;
                }
            }
        }
        
        if( $rerun) {
        	//recurse
         	$this->generateTree();
        }
        
        return;    
  	}

	private function generateCostModifiers() {
		$full_tree = &$this->tree;
		$cost_modifiers=array();
		foreach( $full_tree as $category=>$rules ) {
			foreach($rules as $id ) {
				$rule = $this->rule_list[$id];
				if(empty($rule['cost_modifiers'] ) ) continue;
				$cnt=count($rule['cost_modifiers']);
				
				for($i=0;$i<$cnt;$i++) {
					$cost_modifiers[$rule['cost_modifiers'][$i]['rule_modifies_id']]['cp_cost']=$rule['cost_modifiers'][$i]['cp_cost'];
					$cost_modifiers[$rule['cost_modifiers'][$i]['rule_modifies_id']]['vp_cost']=$rule['cost_modifiers'][$i]['vp_cost'];
				}
			}
		}
		
		return($cost_modifiers);
	}
    
    private function generateXmlTree( ) {
        $full_tree = &$this->tree;  // the skill list generated by generateTree()
        $selected  = &$this->selected;
        $enabled   = &$this->skills_enabled;
        $originals = &$this->originals;
        $player    = &$this->controller->player;
        $game	   = &$this->controller->game_info;    
        $cost_modifiers = $this->generateCostModifiers();
                
        reset($full_tree);
        $xml_output=array();
        
        $xml_output['page']['requestUrl'] = $this->controller->here;
        if( time() <= strtotime( $player['member_until'] ) ) {
            $xml_output['player']['name'] = $player['first_name'] . ' ' . $player['last_name'];
            $xml_output['player']['key'] = $player['id'];
        }
        
        $xml_output['game']['abbr'] = $game['abbr'];
        $xml_output['game']['id'] =   $game['id'];
        $xml_output['game']['name'] = $game['name'];
        
        $xml_output['character']['cp']['unspent']=$this->cp_unspent;
        $xml_output['character']['cp']['spent']=$this->cp_spent;
        $xml_output['character']['vp']['unspent']=$this->vp_unspent;
        $xml_output['character']['vp']['spent']=$this->vp_spent;
        $xml_output['character']['bp']['lives']=$this->num_lives;
        $xml_output['character']['bp']['godsends']=$this->num_godsends;
        $xml_output['character']['bp']['points']=$this->bp_total;
        
        if( $this->character ) {
            $xml_output['character']['key']= $this->character['id'];
            $xml_output['character']['info']['name'] = $this->character['name'];
            $xml_output['character']['info']['level'] = $this->character['level'];
            $xml_output['character']['info']['xp'] = $this->character['xp'];
        } else {
            $xml_output['character']['key']= 0;
            $xml_output['character']['info']['name'] = 'UNKNOWN';
            $xml_output['character']['info']['level'] = 1;
            $xml_output['character']['info']['xp'] = 0;
        }       

        foreach( $full_tree as $category=>$rules ) {
            foreach( $rules as $id ) {
                $info=array();
                $rule = $this->rule_list[$id];
                
                $cat_name = $rule['category']['name'];
                $info['cat_id'] = $rule['category']['id'];
                $info['key'] = $rule['id'];
                $info['name'] = $rule['name'];
                $info['description'] = $rule['description'];
                $info['prereq'] = $rule['prereq'];
                
                if( array_key_exists($id,$cost_modifiers)) {
                	$info['cp']=$cost_modifiers[$id]['cp_cost'];
                	$info['vp']=$cost_modifiers[$id]['vp_cost'];
                } else {
                	$info['cp'] = $rule['cp_cost'];
                	$info['vp'] = $rule['vp_cost'];
                }
                
                if( $rule['is_hidden'] ) $info['hidden'] = 1;
                if( $rule['is_essence'] ) $info['essence'] = 1;
                if( $rule['is_trained'] ) $info['trained'] = 1;
                if( $rule['is_default_rule'] ) $info['default'] = 1;
                
                if( in_array( $id, $selected ) ) {
                    $info['selected']=1;
                }
                
                if( in_array( $id, $enabled ) ) {
                    $info['enabled'] = 1;
                }
                
                // locked
                if( array_key_exists( 'enabled', $info ) && in_array( $id, $originals ) ) {
                    $info['locked']=1;
                } elseif( array_key_exists( 'trained', $info ) && (! in_array( $id, $originals ) ) ) {
                    $info['locked']=1;
                }

                if( $rule['sorting_category_id'] ) {
                    $c = $this->controller->Category->grab( $rule['sorting_category_id'] );
                    $info['categoryMask'] = $c['name'];
                } else {
                    $c = $this->controller->Category->grab( $rule['category_id'] );
                    $info['categoryMask2'] = $c['name'];
                }
                
                $xml_output['tree']['rule'][] = $info;

            }
        }
        
        /**
        echo "<pre>";
        print_r($xml_output);
        echo "</pre>";
        exit(0);
		**/
        return($xml_output);
    }
    
    
    private function runCalculations( $filters=array() ) {
        if( $filters ) {
            $this->filters=$filters;
            $this->selected=$filters;
        }
        
        if( $this->character ) {
            $this->num_lives = $this->character['lives'];
            $this->num_godsends = $this->character['godsends'];
            $this->cp_unspent= $this->character['cp_unspent'];
            $this->vp_unspent = $this->character['vp_unspent'];
            $this->cp_spent   = $this->character['cp_spent'];
            $this->vp_spent   = $this->character['vp_spent'];
            
            //$this->bp_total = $this->character['bp'];
            //RECALC BODY
            $this->bp_total = $this->starting_destiny['base_bp'];
            foreach( $this->selected as $id ) {
                $rule = $this->controller->Rule->grab( $id );
                $mods = $this->controller->unmake_serial($rule['modifiers']);
                $category = $rule['category']['name'];

                if( $mods['bp'] != 0 ) {
                    $this->bp_total += $mods['bp'];
                }
            }
        } else {
            $this->cp_total = $this->starting_destiny['base_cp'] + ( $this->starting_destiny['cp_multiplier'] * $this->level );
            $this->vp_total = $this->starting_destiny['base_vp'] + ( $this->starting_destiny['vp_multiplier'] * $this->level );
            $this->cp_spent = 0;
            $this->vp_spent = 0;
            $this->bp_total = $this->starting_destiny['base_bp'];
            $this->num_lives = $this->starting_lifeforce['base_lives'];
            $this->num_godsends = $this->starting_lifeforce['base_godsends'];

            //*** overall info **//
            
            
            foreach( $this->selected as $id ) {
                $rule = $this->controller->Rule->grab( $id );
                $mods = $this->controller->unmake_serial($rule['modifiers']);
                $category = $rule['category']['name'];

                if( $mods['bp'] != 0 ) {
                    $this->bp_total += $mods['bp'];
                }
                
                if( $mods['cp'] != 0 ) {
                    $this->cp_total += $mods['cp'];
                }
                
                if( $mods['vp'] != 0 ) {
                    $this->vp_total += $mods['vp'];
                }
                
                if( $mods['godsends'] != 0 ) {
                    $this->num_godsends += $mods['godsends'];
                }
                
                if( $mods['lives'] != 0 ) {
                    $this->num_lives += $mods['lives'];
                }
            }
            $this->cp_unspent = $this->cp_total;
            $this->vp_unspent = $this->vp_total;                
        }
        
        if( $this->bp_total < 0 ) $this->bp_total = 1;
        return;
    }
    
    
    
    ///POST METHODS
     
    private function rulesetIsValid( $data ) {
    	/** generate the starting cp and vp **/
    	$this->check_against_callback = &$data;
    	$this->runCalculations($data);
    	
    	$cost_modifiers=array();
    	
    	foreach($data as $id ) {
            $rule = $this->rule_list[$id];
            if($rule['prereq']=='*') continue;
            $str = preg_replace_callback( '/\d+/', array( &$this, 'checkFilters'), $rule['prereq'] );          
            eval( "\$valid=$str;" );

            if(! $valid ) {
                return( FALSE );
            }
            
            if(empty($rule['cost_modifiers'])) continue;
			$cnt=count($rule['cost_modifiers']);
			for($i=0;$i<$cnt;$i++) {
				$cost_modifiers[$rule['cost_modifiers'][$i]['rule_modifies_id']]['cp_cost']=$rule['cost_modifiers'][$i]['cp_cost'];
				$cost_modifiers[$rule['cost_modifiers'][$i]['rule_modifies_id']]['vp_cost']=$rule['cost_modifiers'][$i]['vp_cost'];
			}
        }

        /** see if cost exceeds available totals **/
        $cp_total = 0;
        $vp_total = 0;
        foreach($data as $id) {
        	$rule = $this->rule_list[$id];
        	if(array_key_exists($id,$cost_modifiers) ) {
        		$cp_cost=$cost_modifiers[$id]['cp_cost'];
        		$vp_cost=$cost_modifiers[$id]['vp_cost'];
        	} else {
        		$cp_cost = $rule['cp_cost'];
        		$vp_cost = $rule['vp_cost'];
        	}

                $cp_total += $cp_cost;
                $vp_total += $vp_cost;
        }

        if(($this->vp_unspent + $this->vp_spent) < $vp_total || ($this->cp_unspent + $this->cp_spent) < $cp_total ) {
            return( FALSE );
        }

        $difference = $this->cp_spent - $cp_total;
        $this->cp_spent   = $cp_total;
        $new_cp_unspent = $this->cp_unspent + $difference;
        $this->cp_unspent = $new_cp_unspent;

        $v_difference = $this->vp_spent - $vp_total;
        $this->vp_spent   = $vp_total;
        $new_vp_unspent = $this->vp_unspent + $v_difference;
        $this->vp_unspent = $new_vp_unspent;
        
        return(TRUE);
    }
    	

    function buildPostFields( $data ) {
        $r_data=array();
        if( array_key_exists( 'id', $data ) ) {
            $r_data['Character']['id'] = $data['id'];
        } else {
            if(! $data['name'] ) {
                $data['name'] = 'UNKNOWN' . time();
            }
            $r_data['Character']['name'] = $data['name'];
            $r_data['Character']['level'] = 1;
            $r_data['Character']['xp'] = 0;
        }
        
        $r_data['Character']['cp_unspent'] = $this->cp_unspent;
        $r_data['Character']['cp_spent'] = $this->cp_spent;
        $r_data['Character']['vp_unspent'] = $this->vp_unspent;
        $r_data['Character']['vp_spent'] = $this->vp_spent;
        $r_data['Character']['player_id'] = $this->controller->player['id'];
        $r_data['Character']['game_id'] = $this->controller->game_id;
        $r_data['Character']['lives'] = $this->num_lives;
        $r_data['Character']['godsends'] = $this->num_godsends;
        $r_data['Character']['bp'] = $this->bp_total;
        $r_data['Rule']['Rule'] = $data['rule'];
        $r_data['Bank']['money'] = 0;
        return( $r_data );
    }
    
    
    
    /*** wrapper functions **/
    public function generate( $filters, $mask=array(), $character=array() ) {
        $this->resetTree( );
        $this->mask = $mask;
        $this->character=$character;
        $this->generateTree( $filters );
        $this->runCalculations();
    }
    
    
    /** set basic information for the rules for this game **/
    public function setupGame( $params ) {
        foreach( $params as $key=>$value ) {
            $this->$key = $value;
        }
    }
    
    public function post( $data ) {
        if( $this->rulesetIsValid( $data['rule'] ) ) {
            return( $this->buildPostFields($data) );
        }
        return( FALSE );
    }

    public function refreshCharacter( $data ) {
        $this->character = $data;
        $this->cp_unspent = $data['cp_unspent'];
        $this->cp_spent   = $data['cp_spent'];
        $this->vp_unspent = $data['vp_unspent'];
        $this->vp_spent   = $data['vp_spent'];
    }
    
    public function getXmlArray( ) {
        return( $this->generateXmlTree( ) );
    }
        
}
    
?>