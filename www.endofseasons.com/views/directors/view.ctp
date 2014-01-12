<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
	$html->css( 'jquery-ui.css', 'stylesheet', array( 'media'=>'all'), false );
	$javascript->link( 'jquery', false );
	$javascript->link( 'jquery-ui', false );
?>

<div id="dialog"></div>
<div id="player_view">
    <ul>
        <li><a href="#fragment-1"><span>Character</span></a></li>
        <li><a href="#fragment-2"><span>Player</span></a></li>
        <li><a href="#fragment-3"><span>Event</span></a></li>
        <li><a href="#fragment-4"><span>Misc</span></a></li>
    </ul>
    <div id="fragment-1">
        <div style="width:100%; font-size:12px;">
            <h1>View Character Sheet: </h1>

            <?php
            $c_cnt = count($all_characters);
            echo "<select name=\"v_char\" id=\"v_char\">";
            for ($i=0;$i<$c_cnt;$i++) {
                $char_id = $all_characters[$i]['id'];
                $char_name = $all_characters[$i]['name'];
                echo "<option value=\"$char_id\">$char_name</option>";
            }
            echo "</select><br>";
            $onclick0="viewSheet()";
            ?>
            <a href="javascript:void(0)"<?php echo $onclick0 ? " onclick=\"$onclick0\"" : '' ?>>Submit</a>
            <hr>

            <h1>Add CP to Character: </h1>

            <?php
            $c_cnt = count($all_characters);

            echo "<select name=\"cp_char\" id=\"cp_char\">";
            for ($i=0;$i<$c_cnt;$i++) {
                $char_id = $all_characters[$i]['id'];
                $char_name = $all_characters[$i]['name'];
                echo "<option value=\"$char_id\">$char_name</option>";
            }
            echo "</select><br>";

            echo "<select name=\"cp_num\" id=\"cp_num\">";
            for ($i=1;$i<11;$i++) {
                echo "<option value=\"$i\">$i</option>";
            }
            echo "</select><br>";

            $onclick1="addCP()";
            ?>

            <a href="javascript:void(0)"<?php echo $onclick1 ? " onclick=\"$onclick1\"" : '' ?>>Submit</a>
            <hr>

            <h1>Add VP to Character: </h1>

            <?php
            $c_cnt = count($all_characters);

            echo "<select name=\"vp_char\" id=\"vp_char\">";
            for ($i=0;$i<$c_cnt;$i++) {
                $char_id = $all_characters[$i]['id'];
                $char_name = $all_characters[$i]['name'];
                echo "<option value=\"$char_id\">$char_name</option>";
            }
            echo "</select><br>";

            echo "<select name=\"vp_num\" id=\"vp_num\">";
            for ($i=1;$i<10;$i++) {
                echo "<option value=\"$i\">$i</option>";
            }
            echo "</select><br>";

            $onclick8="addVP()";
            ?>

            <a href="javascript:void(0)"<?php echo $onclick8 ? " onclick=\"$onclick8\"" : '' ?>>Submit</a>
            <hr>

            <h1>Set Level for Character: </h1>

            <?php
            $c_cnt = count($all_characters);

            echo "<select name=\"l_char\" id=\"l_char\">";
            for ($i=0;$i<$c_cnt;$i++) {
                $char_id = $all_characters[$i]['id'];
                $char_name = $all_characters[$i]['name'];
                echo "<option value=\"$char_id\">$char_name</option>";
            }
            echo "</select><br>";

            echo "<select name=\"l_num\" id=\"l_num\">";
            for ($i=1;$i<31;$i++) {
                echo "<option value=\"$i\">$i</option>";
            }
            echo "</select><br>";

            $onclick9="setLevel()";
            ?>

            <a href="javascript:void(0)"<?php echo $onclick9 ? " onclick=\"$onclick9\"" : '' ?>>Submit</a>
                        <hr>
            <h1>Add Director Comment</h1>
            <?php
                $c_cnt = count($all_characters);
                echo "<select name=\"d_char\" id=\"d_char\">";
                    for ($i=0;$i<$c_cnt;$i++) {
                    $char_id = $all_characters[$i]['id'];
                    $char_name = $all_characters[$i]['name'];
                    echo "<option value=\"$char_id\">$char_name</option>";
                }
                echo "</select><br>";
            ?>
                <input type="text" name="d_comment" id="d_comment"/>
            <?php
                $onclick15="directorComment()";
            ?>
            <a href="javascript:void(0)"<?php echo $onclick15 ? " onclick=\"$onclick15\"" : '' ?>>Submit</a>
            <hr>
            <h1>Add Training to Character: </h1>

            <select name="rule_char" id="rule_char">

            <?php
                $c_cnt = count($all_characters);

                for ($i=0;$i<$c_cnt;$i++) {
                    $char_id = $all_characters[$i]['id'];
                    $char_name = $all_characters[$i]['name'];
                    echo "<option value=\"$char_id\">$char_name</option>";
                }
                ?>
            </select><br>

            <select name="rule" id="rule">
                <option value="587">Veteran Race Training</option>
                <option value="588">Heroic Race Training</option>
                <option value="593">GM Archetype Training</option>
                <option value="594">GM Vocation Training</option>

                <option value="596">Initiate of Faya</option>
                <option value="597">Initiate of Soliran</option>
                <option value="598">Initiate of Grumach</option>
                <option value="599">Initiate of Unacia</option>
                <option value="600">Initiate of the Unmaker</option>

                <option value="601">Anointed to Faya</option>
                <option value="602">Anointed to Soliran</option>
                <option value="603">Anointed to Grumach</option>
                <option value="604">Anointed to Unacia</option>
                <option value="605">Anointed to the Unmaker</option>

                <option value="606">Ascended of Faya</option>
                <option value="607">Ascended of Soliran</option>
                <option value="608">Ascended of Grumach</option>
                <option value="609">Ascended of Unacia</option>
                <option value="610">Ascended of the Unmaker</option>

                <option value="611">The Fire is Never Quenched</option>
                <option value="612">The Spirit is Never Tamed</option>
                <option value="613">The Righteous Must Never Fear</option>
            </select><br>";
            <?php
                $onclick5="addRule()";
            ?>

            <a href="javascript:void(0)"<?php echo $onclick5 ? " onclick=\"$onclick5\"" : '' ?>>Submit</a>
            <hr>
            <b>FOR REFACTORING: View Players and Characters</b><br>
            <a href=/director/listPlayersCharacters>Show all players and their characters</a><br>

            <hr>
            <b>Fix Name</b><br>
            <?php
                echo "<select name=\"name_char\" id=\"name_char\">";
                for ($i=0;$i<$c_cnt;$i++) {
                    $char_id = $all_characters[$i]['id'];
                    $char_name = $all_characters[$i]['name'];
                    echo "<option value=\"$char_id\">$char_name</option>";
                }
                echo "</select><br>";
                echo "New Name: <input type=\"text\" id=\"new_name\" name=\"new_name\" /><br />";

                $onclick6="changeName()";
            ?>

            <a href="javascript:void(0)"<?php echo $onclick6 ? " onclick=\"$onclick6\"" : '' ?>>Submit</a>
            <hr>
            <h1>Spend Token for Character</h1>

            <?php
                $c_cnt = count($all_characters);
                $p_cnt = count($all_players);
                $e_cnt = count($all_events);

                echo "<select name=\"s_char\" id=\"s_char\">";
                for ($i=0;$i<$c_cnt;$i++) {
                    $char_id = $all_characters[$i]['id'];
                    $char_name = $all_characters[$i]['name'];
                    echo "<option value=\"$char_id\">$char_name</option>";
                }
                echo "</select><br>";
            ?>
                <select name="s_type" id="s_type">
                    <option value="03D-EVT">3 Day Event</option>
                    <option value="03D-FOD">3 Day Food</option>
                    <option value="04D-EVT">4 Day Event</option>
                    <option value="04D-FOD">4 Day Food</option>
                    <option value="01D-EVT">1 Day Event</option>
                    <option value="01D-FOD">1 Day Food</option>
                </select><br>
            <?php
                echo "<select name=\"s_event\" id=\"s_event\">";
                for ($i=0;$i<$e_cnt;$i++) {
                    $event_id = $all_events[$i]['id'];
                    $event_name = $all_events[$i]['name'];
                    echo "<option value=\"$event_id\">$event_name</option>";
                }
                echo "</select><br>";

                $onclick11="spendToken()";
            ?>
            <a href="javascript:void(0)"<?php echo $onclick11 ? " onclick=\"$onclick11\"" : '' ?>>Submit</a>
            <hr>
            <h1>Delete Character</h1>
            <?php
                $c_cnt = count($all_characters);
                echo "<select name=\"del_char\" id=\"del_char\">";
                for ($i=0;$i<$c_cnt;$i++) {
                    $char_id = $all_characters[$i]['id'];
                    $char_name = $all_characters[$i]['name'];
                    echo "<option value=\"$char_id\">$char_name</option>";
                }
                echo "</select><br>";
                $onclick12="deleteChar()";
            ?>
            <a href="javascript:void(0)"<?php echo $onclick12 ? " onclick=\"$onclick12\"" : '' ?>>Submit</a>
            <hr>
            <h1>Remove Lives</h1>
            <?php
                $c_cnt = count($all_characters);

                echo "<select name=\"g_char\" id=\"g_char\">";
                for ($i=0;$i<$c_cnt;$i++) {
                    $char_id = $all_characters[$i]['id'];
                    $char_name = $all_characters[$i]['name'];
                    $gifts = "Gifts: ".$all_characters[$i]['lives'];
                    $godsends = "Godsends: ".$all_characters[$i]['godsends'];
                    echo "<option value=\"$char_id\">$char_name $gifts $godsends</option>";
                }
                echo "</select><br>";
            ?>
            <strong>Gifts:</strong>
            <select name="g_gifts" id="g_gifts">
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select><br>
            <strong>Godsends</strong>
                <select name="g_godsends" id="g_godsends">
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select><br>
            <?php
                $onclick13="removeLives()";
            ?>
            <a href="javascript:void(0)"<?php echo $onclick13 ? " onclick=\"$onclick13\"" : '' ?>>Submit</a>
        </div>
    </div>
    <div id="fragment-2">
        <div style="width:100%; font-size:12px;">
            <h1>Change Password </h1>
            <?php
            echo "<select name=\"pass_player\" id=\"pass_player\">";
            for ($i=0;$i<$p_cnt;$i++) {
                $player_id = $all_players[$i]['id'];
                $player_name_f = $all_players[$i]['first_name'];
                $player_name_l = $all_players[$i]['last_name'];
                $player_name = $player_name_f . " " . $player_name_l;
                echo "<option value=\"$player_id\">$player_name</option>";
            }
            echo "</select><br>";
            echo "New Password: <input type=\"text\" id=\"new_pass\" name=\"new_pass\" /><br />";
            $onclick21="changePass()";
            ?>
            <a href="javascript:void(0)"<?php echo $onclick21 ? " onclick=\"$onclick21\"" : '' ?>>Submit</a>
            <hr>

            <h1>Add Tokens to Player: </h1>

            <?php
            $p_cnt = count($all_players);

            echo "<select name=\"ev_player\" id=\"ev_player\">";
            for ($i=0;$i<$p_cnt;$i++) {
                $player_id = $all_players[$i]['id'];
                $player_name_f = $all_players[$i]['first_name'];
                $player_name_l = $all_players[$i]['last_name'];
                $player_name = $player_name_f . " " . $player_name_l;
                echo "<option value=\"$player_id\">$player_name</option>";
            }
            echo "</select><br>";

            echo "<select name=\"ev_num\" id=\"ev_num\">";
            for ($i=1;$i<6;$i++) {
                echo "<option value=\"$i\">$i</option>";
            }
            echo "</select><br>";
            ?>
            <select name="ev_tok" id="ev_tok">
                <option value="03D-EVT">3 Day Event</option>
                <option value="03D-FOD">3 Day Food</option>
                <option value="04D-EVT">4 Day Event</option>
                <option value="04D-FOD">4 Day Food</option>
                <option value="01D-EVT">1 Day Event</option>
                <option value="01D-FOD">1 Day Food</option>
            </select><br>

            <?php
            $onclick2="addToken()";
            ?>
            <a href="javascript:void(0)"<?php echo $onclick2 ? " onclick=\"$onclick2\"" : '' ?>>Submit</a>
            <hr>
            <h1>Remove Tokens from Player: </h1>

            <?php
            $p_cnt = count($all_players);

            echo "<select name=\"rem_player\" id=\"rem_player\">";
            for ($i=0;$i<$p_cnt;$i++) {
                $player_id = $all_players[$i]['id'];
                $player_name_f = $all_players[$i]['first_name'];
                $player_name_l = $all_players[$i]['last_name'];
                $player_name = $player_name_f . " " . $player_name_l;
                echo "<option value=\"$player_id\">$player_name</option>";
            }
            echo "</select><br>";

            echo "<select name=\"rem_num\" id=\"rem_num\">";
            for ($i=1;$i<6;$i++) {
                echo "<option value=\"$i\">$i</option>";
            }
            echo "</select><br>";
            ?>
            <select name="rem_tok" id="rem_tok">
                <option value="03D-EVT">3 Day Event</option>
                <option value="03D-FOD">3 Day Food</option>
                <option value="04D-EVT">4 Day Event</option>
                <option value="04D-FOD">4 Day Food</option>
                <option value="01D-EVT">1 Day Event</option>
                <option value="01D-FOD">1 Day Food</option>
            </select><br>

            <?php
            $onclick14="removeToken()";
            ?>
            <a href="javascript:void(0)"<?php echo $onclick14 ? " onclick=\"$onclick14\"" : '' ?>>Submit</a>
            <hr>
            <b>ADD MEMBERSHIP for Player</b><br />
            <?php
                $p_cnt = count($all_players);

                echo "<select name=\"m_player\" id=\"m_player\">";
                for ($i=0;$i<$p_cnt;$i++) {
                    $player_id = $all_players[$i]['id'];
                    $player_name_f = $all_players[$i]['first_name'];
                    $player_name_l = $all_players[$i]['last_name'];
                    $player_name = $player_name_f . " " . $player_name_l;
                    echo "<option value=\"$player_id\">$player_name</option>";
                }
                echo "</select><br>";
            ?>

                <select name="member" id="member">
                    <option value="01Y-MEM">1 year membership</option>
                    <option value="01M-MEM">1 month membership</option>
                </select><br>

            <?php
                $onclick7="addMembership()";
            ?>

            <a href="javascript:void(0)"<?php echo $onclick7 ? " onclick=\"$onclick7\"" : '' ?>>Submit</a>
            <hr>
            <h1>Assign Character to Player</h1>

            <?php
                $c_cnt = count($all_characters);
                $p_cnt = count($all_players);

                echo "<select name=\"a_char\" id=\"a_char\">";
                for ($i=0;$i<$c_cnt;$i++) {
                    $char_id = $all_characters[$i]['id'];
                    $char_name = $all_characters[$i]['name'];
                    echo "<option value=\"$char_id\">$char_name</option>";
                }
                echo "</select><br>";

                echo "<select name=\"a_player\" id=\"a_player\">";
                for ($i=0;$i<$p_cnt;$i++) {
                    $player_id = $all_players[$i]['id'];
                    $player_name_f = $all_players[$i]['first_name'];
                    $player_name_l = $all_players[$i]['last_name'];
                    $player_name = $player_name_f . " " . $player_name_l;
                    echo "<option value=\"$player_id\">$player_name</option>";
                }
                echo "</select><br>";

                $onclick10="assignChar()";
            ?>

            <a href="javascript:void(0)"<?php echo $onclick10 ? " onclick=\"$onclick10\"" : '' ?>>Submit</a>
            <hr>
        </div>
    </div>
    <div id="fragment-3">
        <div style="width:100%; font-size:12px;">
            <h1>Force Event Blanket (note: THIS CAN ONLY BE DONE ONCE): </h1>

            <?php
                $e_cnt = count($all_events);

                echo "<select name=\"ev_blanket\" id=\"ev_blanket\">";
                for ($i=0;$i<$e_cnt;$i++) {
                    if (!$all_events[$i]['ran_blanket']) {
                        $event_id = $all_events[$i]['id'];
                        $event_name = $all_events[$i]['name'];
                        echo "<option value=\"$event_id\">$event_name</option>";
                    }
                }
                echo "</select><br>";

                $onclick3="blanketEvent()";
            ?>
            <a href="javascript:void(0)"<?php echo $onclick3 ? " onclick=\"$onclick3\"" : '' ?>>Submit</a>
        </div>
    </div>
    <div id="fragment-4">
        <div style="width:100%; font-size:12px;">
         <a href=/director/showAuditLog/0>Show Audit Logs (ALL)</a><br>
         <a href=/director/showAuditLog/13>Show Audit Logs (Ken)</a><br>
         <a href=/director/showAuditLog/19>Show Audit Logs (Tommy)</a><br>
         <a href=/director/showAuditLog/14>Show Audit Logs (Ernie)</a><br>
        </div>
    </div>
</div>
<script type="text/javascript">
dia= $("#dialog").dialog({ autoOpen: false, modal: true, show: 'slide' });

function viewSheet() {
    var char_id = document.getElementById('v_char');
    var CharID = char_id.value;

    window.location = "/characters/view/"+CharID;
}

function addCP() {
    var char_id = document.getElementById('cp_char');
    var CharID = char_id.value;
    
    var number = document.getElementById('cp_num');
    var Num = number.value;

    button=$("#"+CharID+"_"+Num );

    $.post( "/director/checkCP",
        {character:CharID,number:Num},
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

function addVP() {
    var char_id = document.getElementById('vp_char');
    var CharID = char_id.value;

    var number = document.getElementById('vp_num');
    var Num = number.value;

    button=$("#"+CharID+"_"+Num );

    $.post( "/director/checkVP",
        {character:CharID,number:Num},
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

function directorComment() {
    var char_id = document.getElementById('d_char');
    var CharID = char_id.value;

    var comment = document.getElementById('d_comment');
    var Comment = comment.value;

    button=$("#"+CharID+"_"+Comment );

    $.post( "/director/checkDirectorComment",
        {character:CharID,comment:Comment},
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

function setLevel() {
    var char_id = document.getElementById('l_char');
    var CharID = char_id.value;

    var number = document.getElementById('l_num');
    var Num = number.value;

    button=$("#"+CharID+"_"+Num );

    $.post( "/director/checkLevel",
        {character:CharID,number:Num},
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

function addRule() {
    var char_id = document.getElementById('rule_char');
    var CharID = char_id.value;
    
    var rule = document.getElementById('rule');
    var Rule = rule.value;

    button=$("#"+CharID+"_"+Rule );

    $.post( "/director/checkAddRule",
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

function changePass() {
    var player_id = document.getElementById('pass_player');
    var PlayerID = player_id.value;

    var password = document.getElementById('new_pass');
    var Password = password.value;

    button=$("#"+PlayerID+"_"+ Password );

    $.post( "/director/checkResetPassword",
        {player:PlayerID,password:Password},
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

function addToken() {
    var player_id = document.getElementById('ev_player');
    var PlayerID = player_id.value;

    var number = document.getElementById('ev_num');
    var Num = number.value;

    var token = document.getElementById('ev_tok');
    var Type = token.value;

    button=$("#"+PlayerID+"_"+Num );

    $.post( "/director/checkToken",
        {player:PlayerID,number:Num,type:Type},
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

function removeToken() {
    var player_id = document.getElementById('rem_player');
    var PlayerID = player_id.value;

    var number = document.getElementById('rem_num');
    var Num = number.value;

    var token = document.getElementById('rem_tok');
    var Type = token.value;

    button=$("#"+PlayerID+"_"+Num );

    $.post( "/director/checkRemoveToken",
        {player:PlayerID,number:Num,type:Type},
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

function blanketEvent() {
    var event_id = document.getElementById('ev_blanket');
    var EventID = event_id.value;

    button=$("#"+EventID );

    $.post( "/director/checkBlanket",
        {event:EventID},
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

function addTraining() {
    var char_id = document.getElementById('t_char');
    var CharID = char_id.value;

    var training = document.getElementById('t_train');
    var Train = training.value;

    button=$("#"+CharID+"_"+Train );

    $.post( "/director/checkTraining",
        {character:CharID,train:Train},
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

function addMembership() {
    var player_id = document.getElementById('m_player');
    var PlayerID = player_id.value;

    var token = document.getElementById('member');
    var Type = token.value;

    button=$("#"+PlayerID+"_"+Type );

    $.post( "/director/checkMembership",
        {player:PlayerID,membership:Type},
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

function changeName() {
    var char_id = document.getElementById('name_char');
    var CharID = char_id.value;

    var name = document.getElementById('new_name');
    var Name = name.value;

    button=$("#"+CharID+"_"+Name );

    $.post( "/director/checkChangeName",
        {character:CharID,name:Name},
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

function assignChar() {
    var char_id = document.getElementById('a_char');
    var CharID = char_id.value;

    var play_id = document.getElementById('a_player');
    var PlayerID = play_id.value;

    button=$("#"+CharID+"_"+PlayerID );

    $.post( "/director/checkAssign",
        {character:CharID,player:PlayerID},
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

function spendToken() {
    var char_id = document.getElementById('s_char');
    var CharID = char_id.value;

    var token = document.getElementById('s_type');
    var Type = token.value;

    var event_id = document.getElementById('s_event');
    var EventID = event_id.value;

    button=$("#"+CharID+"_"+Type+"_"+EventID );

    $.post( "/director/checkSpendToken",
        {character:CharID,type:Type,event:EventID},
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

function deleteChar() {
    var char_id = document.getElementById('del_char');
    var CharID = char_id.value;

    button=$("#"+CharID);

    $.post( "/director/checkDeleteChar",
        {character:CharID},
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

function removeLives() {
    var char_id = document.getElementById('g_char');
    var CharID = char_id.value;

    var gift = document.getElementById('g_gifts');
    var Gift = gift.value;

    var godsend = document.getElementById('g_godsends');
    var Godsend = godsend.value;

    button=$("#"+CharID+"_"+Gift+"_"+Godsend);

    $.post( "/director/checkRemoveLives",
        {character:CharID,gifts:Gift,godsends:Godsend},
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
		}
	});
	dia.dialog('open');
}


</script>