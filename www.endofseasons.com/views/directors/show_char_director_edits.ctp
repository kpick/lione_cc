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
    echo "Remove Edit:<br>";
    echo "<select name=\"edit\" id=\"edit\">";
    for($i = 0; $i < count($edits); $i++) {
        $edit_description = $edits[$i]['description'];
        $edit_id = $edits[$i]['id'];
        echo "<option value=\"$edit_id\">$edit_description</option>";
    }
    echo "</select><br>";
    $onclick="removeEdit()";
?>

<a href="javascript:void(0)"<?php echo $onclick ? " onclick=\"$onclick\"" : '' ?>>Submit</a>
<hr>
<script type="text/javascript">
 dia= $("#dialog").dialog({ autoOpen: false, modal: true, show: 'slide' });

function removeEdit() {
    var CharID = <?= $char_id ?>;

    var edit = document.getElementById('edit');
    var Edit = edit.value;

    button=$("#"+CharID+"_"+Edit );

    $.post( "/director/checkRemoveEdit",
        {character:CharID,edit:Edit},
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