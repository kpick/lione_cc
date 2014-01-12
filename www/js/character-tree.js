
function ruleManager() {
    ruleLookup = {};
    selects = Array(); // the skills that have been selected
    member  = false;

    
    this.ruleInit = function(Type, ruleData, characterData,playerData) {
        pageMode = Type;
        ruleTree = ruleData;
        treeSize = ruleData.length;
        characterTree = characterData;
        
        if( playerData != null ) {
            if( playerData.member == 'true') {
               member = true;
            }
        } 
        

        // map a lookup array and set up preproc
        for( var i=0; i < treeSize; ++i ) {
            var rule = ruleTree[i];
			ruleLookup[rule.id] = rule;
			
			if( rule.is_selected == 'true' ) {
			    selects.push(rule.id);
			}
        }

        resetEnabled();
        toggleDisplay();
        
    }
    
    this.selectRule = function(id) {
        selectRule(id);
    }
    
    this.makeTooltip = function(id) {
    	makeTooltip( id );
    }
    
    this.send = function(form_id) {
        send(form_id);
    }
    
    
}

function selectRule(id) {
    var rule = ruleLookup[id];
    if(!rule) return;    

    if( jQuery.inArray( id.toString(), selects ) > -1 ) {
        if( rule.is_selected == 'true' && rule.locked == 'true' ) return;
        removeRule(id);
    } else {
        if( isValid(id)) {
            /** handle single instances **/
            if( pageMode == 'core' ) {
                var l = selects.length;
                for(var i=0; i<l; i++ ) {
                    var rulecmp = ruleLookup[selects[i]];
                    if(!rulecmp) break;
                    if( rulecmp.category == rule.category ) {
                          removeRule(selects[i]);
                    }
                }
                cleanSelects();
            }
            
            if( ( characterTree.cp_unspent - rule.cp_cost ) < 0 ) return;
            if( ( characterTree.vp_unspent - rule.vp_cost ) < 0 ) return;
            if( rule.enabled == 'false' && rule.locked == 'true' ) return;
            addRule(id);
        }
    }
    toggleDisplay();    
}

function addRule(id) {
    var rule = ruleLookup[id];
    if(!rule) return;    
    selects.push(id.toString());
    if( pageMode=='aspect' ) {
        characterTree.cp_unspent = parseInt(characterTree.cp_unspent) - parseInt(rule.cp_cost);
        characterTree.vp_unspent = parseInt(characterTree.vp_unspent) - parseInt(rule.vp_cost);
    }
    resetEnabled();
}

function removeRule(id) {
    var rule = ruleLookup[id];
    if(!rule) return;    
    
    var rem = selects.splice( jQuery.inArray( id.toString(), selects ), 1 );
    if( rem.length > 0 ) {
        characterTree.cp_unspent = parseInt(characterTree.cp_unspent) + parseInt(rule.cp_cost);
        characterTree.vp_unspent = parseInt(characterTree.vp_unspent) + parseInt(rule.vp_cost);
    }

    cleanSelects();
    resetEnabled();
}



function cleanSelects() {
    var len=selects.length;
    for(var i=0; i < len; ++i ) {
        if(! isValid(selects[i])) {
           return removeRule(selects[i]);
        }
    }

    return;
}

function resetEnabled() {
    enabled=Array();
    for( var i=0; i < treeSize; ++i ) {
        var rule = ruleTree[i];
        if( jQuery.inArray( rule.id, selects ) > -1 ) continue;        
        if( pageMode=='aspect') {
            if( rule.enabled == 'false' && rule.locked == 'true' ) continue;
            if( rule.cp_cost > characterTree.cp_unspent ) continue;
            if( rule.vp_cost > characterTree.vp_unspent ) continue;
        }
            
        if( isValid(rule.id) ) {
            if( rule.essence == 'true' ) {
                return addRule(rule.id);
            }
            
            enabled.push(rule.id);
        } 
    }
}

function toggleDisplay() {
    $("span[id$=_rule]").each(function() {
        var ident=$(this).attr('id').split('_');
        $(this).removeClass();

        if( jQuery.inArray( ident[0], enabled ) > -1 ) {
            $(this).addClass('rule-enabled');
        } else if( jQuery.inArray( ident[0], selects) > -1 ) {
            $(this).addClass('rule-selected');
        } else {
            $(this).addClass('rule-disabled');
        }
     });
     
     if( pageMode=='aspect') {
        $("#cp_unspent").html(characterTree.cp_unspent);
        $("#vp_unspent").html(characterTree.vp_unspent);
    }
}


function showTooltip( id ) {
    var tip=$("#"+id+"_tooltip").html();
    $("#toolTip").html(tip);
}

function isValid(id,t) {
    var rule = ruleLookup[id];
    if(!rule) return;
        
    if( rule.prereq == '*' ) return true;
    var str = rule.prereq.replace( /\d+/gi, function(ident) {
        if( jQuery.inArray( ident, selects ) > -1 ) {
            return 1;
        } else {
            return 0;
        }
    });
    
    
    return( eval( str ) );
}

function send(form_id) {
    if( member || pageMode == 'core' ) {
        var l=selects.length;
        for(i=0;i<l;i++) {
            $("#CharacterRule_"+selects[i]).attr('checked', true);
        }
    
        $("#"+form_id).submit();
    } else {
        alert( 'You must be a member if you wish to save a character' );
    }
   

}
