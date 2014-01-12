<?php
class RulesController extends AppController {
    var $name = 'Rules';
	var $helpers = array('Session','Javascript','Html');
	var $components = array( 'RequestHandler' );
	var $hasOne = array('Category');
	var $hasMany = array( 'Cost');
	


	function admin_index() {
		$this->set('rules', $this->Rule->find('all') );
	
	}
	
	function admin_add() {
		if(empty($this->data)) {
			$this->Rule->Category->order='name ASC';
			$this->set( 'categories', $this->Rule->Category->find('list'));	
			$this->set( 'opts', array('OR'=>'any', 'AND'=>'all') );
			$this->set( 'rules',$this->Rule->find('list'));
			$this->set( 'costs',$this->data['Cost'] );
		} else {
			// build the prereq and modifiers 
			$data=$this->data['Rule'];
			$bool_map= array('AND'=>'&', 'OR'=>'||' );
			
			// modifiers is a serialized array
			$modifiers=array('bp'=>$data['mod_bp'], 'cp'=>$data['mod_cp'], 'vp'=>$data['mod_vp'], 'godsends'=>$data['mod_godsend'], 'lives'=>$data['mod_life']);
			$this->data['Rule']['modifiers']=$this->make_serial($modifiers);
			
			//prereqs is ... different
			$rules=array();
			$tmp=array();
			$prereqs='()';
			if( count($data['rules']) ) {
				// clean any empty values
				foreach($data['rules'] as $idx=>$ruleset) {
					foreach($ruleset as $rule_id ) {
						if(! $rule_id ) continue;
						$tmp[$idx][]=$rule_id;
					}
				}
				
				// carry on...
				foreach($tmp as $idx=>$ruleset ) {
					if(count($tmp[$idx])==0 ) continue;
					$rules[]='('.implode($bool_map[$data['bool'][$idx]],$ruleset) .')';
				}
				
				if($data['master_bool']=='SINGLE' ) {
					$prereqs = $rules[0];
				} else {
					$prereqs = '('.implode($bool_map[$data['master_bool']], $rules ) . ')';
				}
			}
			
			$this->data['Rule']['prereq'] = $prereqs;
                        $this->data['Rule']['game_id'] = 1;
			unset($this->data['Rule']['master_bool'] );
			unset($this->data['Rule']['bool'] );
			unset($this->data['Rule']['rules'] );
			
			$this->Rule->save($this->data);
			// handle costs
			$this->Rule->Cost->deleteAll(array('rule_id'=>$this->Rule->id));
			if(array_key_exists('Cost',$this->data ) ) {
				$cnt=count($this->data['Cost']['rule_modifies_id']);
				for($i=0;$i<$cnt;$i++) {
					$this->Rule->Cost->create();
					$arr=array();
					$arr['rule_id']=$this->Rule->id;
					$arr['rule_modifies_id']=$this->data['Cost']['rule_modifies_id'][$i];
					$arr['cp_cost']=$this->data['Cost']['cp_cost'][$i];
					$arr['vp_cost']=$this->data['Cost']['vp_cost'][$i];
					$this->Rule->Cost->save($arr);
				}
			}			
			
			if(isset($id)) {
				$this->Session->setFlash("This rule has been saved" );
			} else {
				$this->Session->setFlash("This rule has been created");
			}
			
			$this->redirect('/admin/rules');
		}
	
	}
	
	function admin_edit($id=null) {
		if (empty($this->data)) {
			$this->data = $this->Rule->findById($id);
			$this->set( 'prereqs',$this->parse_prereqs($this->data['Rule']['prereq']));            
			$this->set( 'rules',$this->Rule->find('list'));
			$this->set( 'categories', $this->Rule->Category->find('list'));	
			$this->set( 'modifiers', $this->unmake_serial($this->data['Rule']['modifiers'] ) );
			$this->set( 'opts', array('OR'=>'any', 'AND'=>'all') );
			$this->set( 'costs',$this->data['Cost'] );
		} else {			
			// build the prereq and modifiers 
			$data=$this->data['Rule'];
			$bool_map= array('AND'=>'&', 'OR'=>'||' );
			
			// modifiers is a serialized array
			$modifiers=array('bp'=>$data['mod_bp'], 'cp'=>$data['mod_cp'], 'vp'=>$data['mod_vp'], 'godsends'=>$data['mod_godsend'], 'lives'=>$data['mod_life']);
			$this->data['Rule']['modifiers']=$this->make_serial($modifiers);
			
			//prereqs is ... different
			$rules=array();
			$tmp=array();
			$prereqs='()';
			if( count($data['rules']) ) {
				// clean any empty values
				foreach($data['rules'] as $idx=>$ruleset) {
					foreach($ruleset as $rule_id ) {
						if(! $rule_id ) continue;
						$tmp[$idx][]=$rule_id;
					}
				}
				
				// carry on...
				foreach($tmp as $idx=>$ruleset ) {
					if(count($tmp[$idx])==0 ) continue;
					$rules[]='('.implode($bool_map[$data['bool'][$idx]],$ruleset) .')';
				}
				
				if($data['master_bool']=='SINGLE' ) {
					$prereqs = $rules[0];
				} else {
					$prereqs = '('.implode($bool_map[$data['master_bool']], $rules ) . ')';
				}
			}
			
			$this->data['Rule']['prereq'] = $prereqs;
			unset($this->data['Rule']['master_bool'] );
			unset($this->data['Rule']['bool'] );
			unset($this->data['Rule']['rules'] );
			
			$this->Rule->save($this->data);
			
			// handle costs
			$this->Rule->Cost->deleteAll(array('rule_id'=>$this->Rule->id));
			if(array_key_exists('Cost',$this->data ) ) {
				$cnt=count($this->data['Cost']['rule_modifies_id']);
				for($i=0;$i<$cnt;$i++) {
					$this->Rule->Cost->create();
					$arr=array();
					$arr['rule_id']=$this->Rule->id;
					$arr['rule_modifies_id']=$this->data['Cost']['rule_modifies_id'][$i];
					$arr['cp_cost']=$this->data['Cost']['cp_cost'][$i];
					$arr['vp_cost']=$this->data['Cost']['vp_cost'][$i];
					$this->Rule->Cost->save($arr);
				}
			}
			
			if($id) {
				$this->Session->setFlash("This rule has been saved" );
			} else {
				$this->Session->setFlash("This rule has been created");
			}
			
			$this->redirect('/admin/rules');
		}
	}
	


	private function parse_prereqs($prereq,&$arr=array()) {
		preg_match_all('/\([0-9]+.*\)/sU', $prereq,$matches);
		$results=$matches[0];
		$bool='';
		$chars=array();
		
		if(count($results)==1) {
			$r=str_replace( array('(',')'), array('',''), $results[0] );
			$split = preg_split('/(&|\|\|)/sU', $r, -1, PREG_SPLIT_DELIM_CAPTURE );
			foreach($split as $val) {
				if( (int ) $val ) {
					$chars[] = $val;
				} elseif( $val == '&' ) {
					$bool='AND';
				} elseif( $val == '||' ) {
					$bool = 'OR';
				}
			}
			$return['SINGLE'][0][$bool] = $chars;
		} else {
			$split = preg_split('/\).*(&|\|\|)\(/sU', $prereq, -1, PREG_SPLIT_DELIM_CAPTURE );
			foreach($split as $val ) {
				if($val=='&') {
					$bool='AND';
					break;
				} elseif($val = '||' ) {
					$bool='OR';
					break;
				} 
			}
			$main=$bool;
			foreach($results as $res) {
				$r=str_replace( array('(',')'), array('',''), $res );
				$split = preg_split('/(&|\|\|)/sU', $r, -1, PREG_SPLIT_DELIM_CAPTURE );
				foreach($split as $val) {
					if( (int ) $val ) {
						$chars[] = $val;
					} elseif( $val == '&' ) {
						$bool='AND';
					} elseif( $val == '||' ) {
						$bool = 'OR';
					}
				}
				
				$return[$main][]=array($bool=>$chars);
				$chars=array();
			}
		}
		
		return($return);
		
	
	}
	
	

}
?>