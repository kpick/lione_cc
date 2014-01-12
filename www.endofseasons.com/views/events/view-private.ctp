<?php 

	//TODO: Handle play-remove and script-remove
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
	$html->css( 'jquery-ui.css', 'stylesheet', array( 'media'=>'all'), false );
	$javascript->link( 'jquery', false );
	$javascript->link( 'jquery-ui', false );
?>

<div id="dialog"></div>

<a href="javascript:void(0)" id="show-more">show more</a>
<br><br>
Select Character you wish to use to book this event:

<?php

    $c_cnt = count($characters);

    //echo "$form->create($characters)";

    echo "<select name=\"event_char\" id=\"event_char\">";
    for ($i=0;$i<$c_cnt;$i++) {
        $char_id = $characters[$i]['id'];
        $char_name = $characters[$i]['name'];
        echo "<option value=\" $char_id \"> $char_name </option>";
    }
    echo "</select>";
?>

<table id="event-table">
<tr>
	<th></th>
	<th>Play/Earn XP</th>
	<th>Script</th>
        <th>Food</th>
</tr>

<?php 
	$cnt = count($events);
	$events_past=0;
	for($i=0;$i<$cnt;$i++) {
		$scripted=false;
                $buy_food=false;
		$buy_xp=false;
		$script_position=-1;
		$played=false;
		
		/** determine if this is an active event **/
		if( strtotime( $events[$i]['start_date'] ) > time() ) {
			$buy_xp=true;
		} elseif( $events_past < $game[0]['event_buyback'] ) {
			$buy_xp=true;
			$events_past++;
		}		
		/** did this person script this event, or are they signed up to script? **/
		if( array_key_exists( $events[$i]['id'], $script_info ) ) {
			if( $script_info[$events[$i]['id']] > -1 ) {
				$scripted=true;
				if(! $events_past ) {
					$script_position = $script_info[$events[$i]['id']]; 
				}
			}
		}
				
		for($j=0;$j<$c_cnt;$j++) {
			$characters[$j]['xp']=false;
			if( array_key_exists( $events[$i]['id'], $play_info ) ) {
				$cinfo = $play_info[$events[$i]['id']];
				foreach($cinfo as $cid ) {
                                    if( $cid == $characters[$j]['id'] ) {
                                        $played=true;
                                        $characters[$j]['xp']=true;
                                    }
				}
			}
		}

		if( array_key_exists( $events[$i]['id'], $food_info ) ) {
                    if( $food_info[$events[$i]['id']] == false ) {
                        $buy_food=true;
                    }
		}
?>


<tr>
<td>
<a href="#"><?=$events[$i]['name'] ?></a>
(<?= date( 'm/d/y', strtotime( $events[$i]['start_date'] ) ) ?> to <?= date( 'm/d/y', strtotime( $events[$i]['end_date'] ) ) ?>)
</td>

<td>
<?php 
	$img_name='cancel.png';
	$onclick='';

        if(! $events_past ) {
                if( $played ) {
                        $img_name = "tick.png";
                        $onclick="reserve({$events[$i]['id']},'play')";
                } else {
                        $img_name = "add.png";
                        $onclick="reserve({$events[$i]['id']},'play')";
                }
        } else { //past events
                if( $played ) {
                        $img_name = "tick.png";
                        $onclick="reserve({$events[$i]['id']},'play')";
                } elseif( $buy_xp ) {
                        $img_name = "add.png";
                        $onclick="reserve({$events[$i]['id']},'play')";
                }
        }
		
?>

<a href="javascript:void(0)"<?php echo $onclick ? " onclick=\"$onclick\"" : '' ?>>
<img id="<?=$events[$i]['id']?>_play" src="/img/icons/<?=$img_name ?>" />
</a>
</td>


<td>
<?php 
	$img_name='cancel.png';
	$onclick='';
	
        if( $scripted ) {
            //$onclick="reserve({$events[$i]['id']},'script-remove')";
            $img_name = "tick.png";
        } else if(  time() > strtotime($events[$i]['end_date'])) {

        } else if ($played) {

        } else {
            $onclick="reserve({$events[$i]['id']},'script')";
            $img_name = "add.png";
        }
?>

<a href="javascript:void(0)"<?php echo $onclick ? " onclick=\"$onclick\"" : '' ?>>
<img id="<?=$events[$i]['id']?>_script" src="/img/icons/<?=$img_name ?>" />
</a>
</td>

<td>
<?php
	$img_name='cancel.png';
	$onclick='';
        if (!$played) {

        } else if( !$buy_food ) {
            $img_name = "tick.png";
        } else {
            $onclick="reserve({$events[$i]['id']},'food')";
            $img_name = "add.png";
        }
?>

<a href="javascript:void(0)"<?php echo $onclick ? " onclick=\"$onclick\"" : '' ?>>
<img id="<?=$events[$i]['id']?>_script" src="/img/icons/<?=$img_name ?>" />
</a>
</td>


</tr>

<?php  
} // endforeach 
?>
</table>

<script type="text/javascript">
dia= $("#dialog").dialog({ autoOpen: false, modal: true, show: 'slide' });

function reserve( EventID, Type, CharID ) {
	var char_name = document.getElementById('event_char');
        CharID = char_name.value;
        button=$("#"+EventID+"_"+Type );
	
	$.post( "/events/check",
			{event:EventID,method:Type,character:CharID},
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

function doConfirmed() {
	$.post( "/events/reserve", function(json_return) {
		data = JSON.parse(json_return);
		sendMessage(data);
	});
}

function sendConfirm(data ) {
	$("#dialog").html(data.message);
	dia.dialog('option', 'title', data.title);
	dia.dialog('option', 'buttons', { 
		"Yes": function() {
			$(this).dialog("close");
			doConfirmed();
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