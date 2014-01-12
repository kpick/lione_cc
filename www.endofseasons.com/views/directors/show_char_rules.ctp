<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
	$html->css( 'jquery-ui.css', 'stylesheet', array( 'media'=>'all'), false );
	$javascript->link( 'jquery', false );
	$javascript->link( 'jquery-ui', false );
?>

<div id="dialog"></div>
<?php
    $char_name = $character['name'];
    $char_id = $character['id'];
    echo "<strong>$char_name</strong><br> <a href=\"/characters/view/$char_id\">";
    echo "View/Edit Character</a> <br>";
    echo "Remove Rule:<br>";
    echo "<select name=\"rule\" id=\"rule\">";
    for($i = 0; $i < count($rules); $i++) {
        $rule_name = $rules[$i]['name'];
        $rule_id = $rules[$i]['id'];
        echo "<option value=\"$rule_id\">$rule_name</option>";
    }
    echo "</select><br>";
    $onclick="removeRule()";
?>

<a href="javascript:void(0)"<?php echo $onclick ? " onclick=\"$onclick\"" : '' ?>>Submit</a>

<hr>
<strong>NOTE1: Refresh Page After Removal to Update List</strong><br>
<strong>NOTE2: Removing abilities tied to race (like weapon discounts) will refund wrong amount of CP</strong>


<script type="text/javascript">
 dia= $("#dialog").dialog({ autoOpen: false, modal: true, show: 'slide' });

function removeRule() {;
    var CharID = <?= $char_id ?>;

    var rule = document.getElementById('rule');
    var Rule = rule.value;

    button=$("#"+CharID+"_"+Rule );

    $.post( "/director/checkRemoveRule",
        {character:CharID,rule:Rule},
            function(json_return){
                data = JSON.parse(json_return);
                if( data.need_confirm ) {
                    sendConfirm(data);
                } else {
                    sendMessage(data);
                }
            }
        );
}


function sendConfirm(data ) {
	$("#dialog").html(data.message);
	dia.dialog('option', 'title', data.title);
	dia.dialog('option', 'buttons', {
		"Yes": function() {
			$(this).dialog("close");
		},
		"No": function() {
			$(this).dialog("close");
		}
	});
	dia.dialog('open');
}

function sendMessage(data) {
	if(data.success) {
		s = button.attr("src", "/img/icons/tick.png");
	}
	$("#dialog").html(data.message);
	dia.dialog('option', 'title', data.title);
	dia.dialog('option','buttons',{
		"OK" : function() {
			$(this).dialog("close");
                        location.reload(true);
		}
	});
	dia.dialog('open');
}

</script>