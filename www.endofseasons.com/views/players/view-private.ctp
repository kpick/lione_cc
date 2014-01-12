<?php
	$html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
?>
<div id="player_view">
            <ul>
                <li><a href="#fragment-1"><span>Profile</span></a></li>
                <li><a href="#fragment-2"><span>Campaigns</span></a></li>
                <li><a href="#fragment-3"><span>Characters</span></a></li>
                <li><a href="#fragment-4"><span>Messages</span></a></li>
            <?php if ($login_info['admin']) { ?>
                <li><a href="#fragment-5"><span>Admin</span></a></li>
            <?php } ?>

            </ul>
            <div id="fragment-1">
            <div style="width:100%; font-size:12px;">
                <div style="float: left; width: 20%;">
                <?=$login_info['first_name'] ?> <?=$login_info['last_name'] ?><br />
                
                <? echo $login_info['address'] ? $login_info['address'] : '<i>no address given</i>' ?><br />
                <? echo $login_info['city'] ? $login_info['city'] . ', ' : '<i>no city, </i>' ?> 
                <? echo $login_info['state'] ? $login_info['state']  : '<i>no state</i>' ?> 
                <? echo $login_info['zipcode'] ? $login_info['zipcode']  : '<i>no zip</i>' ?>
                
                <p>
                <b>Contact</b><br />
                Email: <?=$login_info['email'] ?><br />
                Phone: <? echo $login_info['phone'] ? $login_info['phone']  : '<i>none</i>' ?><br />
                
                </p>
                
                
                [<a href="edit">edit</a>]  |  [<a href="edit/password">change password</a>]
                </div>
                
                <div style="float: left; width: 35%;">
                    <?php if(! $is_member ) { ?>
                    <p><strong>You are not currently a member.</strong></p><p><a href="/cart/add/01Y-MEM">Join now (1 year)!</a></p>
                    <p><a href="/cart/add/01M-MEM">Join now (1 month)!</a></p>
                    <?php } else if( $is_expired ) { ?>
                    <p><strong>Your membership has expired.</strong></p><p><a href="/cart/add/01Y-MEM">Re-up here! (1 year)</a></p>
                    <p><a href="/cart/add/01M-MEM">Re-up here! (1 month)</a></p>
                    <?php } else { ?>
                        Member Until: <strong> <?= date( 'm/d/Y', strtotime( $login_info['member_until'] ) )  ?>
                    <?php } ?></strong><br />
                    <strong>2012 IMPERIAL Package</strong><br>(5 events + 1 free event blanket)
                    (<a href="/cart/add/01-IMP">buy one</a>)<br />
                    <strong>2012 Meal Package</strong><br>(5 events of food at discount rate)
                    (<a href="/cart/add/02-IMP">buy one</a>)<br />
                </div>        
                <div style="float: left; width: 22%;">
                    <strong>EVENT PURCHASES:</strong><br />
                    <strong><?= $login_info['kitchen_cash']?></strong> Kitchen Kash 
                    (<a href="/cart/add/01-SNCK">buy more</a>)<br />
                    <strong><?=$login_info['event_token_3_day'] ?></strong> 3 Day Event Token
                    (<a href="/cart/add/03D-EVT">buy</a>)  <br />
                    <strong><?=$login_info['food_token_3_day'] ?></strong>  3 Day Food Token
                    (<a href="/cart/add/03D-FOD">buy</a>) <br />   
                </div>
                <div style="float: left; width: 23%;">
                    <strong>STORAGE:</strong><br />
                    Small Tote: 1' x 1.5' x 2' and under:
                    (<a href="/cart/add/SM-STO">buy</a>)<br />
                    Large Tote: 1.5' x 1.5' x 2' and under:
                    (<a href="/cart/add/LG-STO">buy</a>)  <br />
                    XL Tote: (over 1.5' x 1.5' x 2'):
                    (<a href="/cart/add/XL-STO">buy</a>) <br />
                    1 Handed Weapon Storage:
                    (<a href="/cart/add/1H-STO">buy</a>) <br />
                    2 Handed Weapon Storage:
                    (<a href="/cart/add/2H-STO">buy</a>) <br />
                    Shield Storage:
                    (<a href="/cart/add/SH-STO">buy</a>) <br />
                </div>
            </div>     
            </div>
            
            <div id="fragment-2">
            <?php if( empty( $characters ) ) { ?>
            <em>You currently have no characters in any campaign - you should 
            <a href="/games/select">create one<?php if( $is_member ) { ?>!</a>
           		<?php } else { ?>, but you'll need to <a href="/cart/add/01Y-MEM">be a member</a> 
           		to save characters.
           		<?php } ?></em>
 				
 				<?php } else { 
 					foreach( $gamesAll as $game ) {
           				echo "<li><a href=\"/events/{$game['abbr']}\">{$game['name']}</a></li>"; 
           			}
 				}		
           		?>
           	</div>
            
            <div id="fragment-3">
            <?php if(! $is_member ) { ?>
            Please feel free to use the <a href="/characters/add">character generator</a>, but you need to <a href="/cart/add/01Y-MEM">be a member</a> to save characters.
            <?php } else {
                
                if( $is_expired ) { 
            ?>
            <p>Your account has expired - you won't be able to save or edit any characters 
            until you <a href="/cart/add/01Y-MEM">renew</a></p>
            <?php }    
                    if( empty( $characters ) ) {
           ?>
        <em>You currently have no characters created</em>
        <?php } else { ?>
        
        <table width="50%">
            <thead>
                <tr><th>Campaign</th><th>Name</th><th>Events Played</th></tr>
            </thead>
            
            <tbody>
        <?php if ($characters[0]) { ?>
            <?php foreach( $characters as $character ) {
                ?>
                <tr style="text-align:left">
                <td><a href="/games/<?=$character['game_id'] ?>"><?=$games[$character['game_id']] ?></a></td>
                <td><a href="/characters/view/<?=$character['id'] ?>"><?=$character['name'] ?></a></td>
                <td><?=$character['level'] - 1 ?></td>
                </tr>
            <?php }
        }
        ?>

        </tbody>
        
        <?php } ?>
        </table>
        
        <p><a href="/characters/add">Create new character</a></p>           
    <?php } ?>
    </div>

    <div id="fragment-4">
        1/16/2011: Welcome to the Character Creator. Please feel free to view our instructions online
        at the <a href="http://lione5.endofseasons.com/wiki/">LIONE wiki</a>.
    </div>

    <?php
    // Admin Panel
    if ($login_info['admin']) { ?>
        <div id="fragment-5">
            <a href="/reports/view">Reports</a><br>
            <a href="/director/view">Director Actions</a> <br>
            <a href="/admin/rules">Administer Rules *</a>
            <br>
            <br>
            <br>
            * (Don't touch unless you know what you're doing)
        </div>
    <?php  } ?>

</div>