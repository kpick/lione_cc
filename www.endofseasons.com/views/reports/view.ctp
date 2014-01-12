<?php 
    $html->css( '2-column.css', 'stylesheet', array( 'media'=>'all'), false );
    $html->css( 'jquery-ui.css', 'stylesheet', array( 'media'=>'all'), false );
    $javascript->link( 'jquery', false );
    $javascript->link( 'jquery-ui', false );

    $cnt = count($events);

?>
<div id="player_view">
    <ul>
        <li><a href="#fragment-1"><span>Event</span></a></li>
        <li><a href="#fragment-2"><span>General</span></a></li>
        <li><a href="#fragment-3"><span>Transactions</span></a></li>
        <li><a href="#fragment-4"><span>Misc</span></a></li>
    </ul>
    <div id="fragment-1">
        <div style="width:100%; font-size:12px;">
            <b>Player List for Event</b><br>

            <?php
            for ($i=0;$i<$cnt;$i++) {
                $id = $events[$i]['id'];
                $name = $events[$i]['name'];
                echo "<a href=\"/reports/showPlayers/$id\"> $name </a><br>";
            }
            ?>
            <hr>
            <b>Transactions for Event</b><br>
            <?php
            for ($i=0;$i<$cnt;$i++) {
                $id = $events[$i]['id'];
                $name = $events[$i]['name'];
                echo "<a href=\"/reports/showTransactions/$id\"> $name </a><br>";
            }
            ?>
        </div>
    </div>
    <div id="fragment-2">
        <div style="width:100%; font-size:12px;">
            <hr>
            <b>Kitchen Cash</b><br>
            <a href=/reports/kitchenCash> Kitchen Cash for All Players </a><br>
            <hr>
            <b>All Player Personal Info</b><br>
            <a href=/reports/listPlayers> List Information for All Players </a><br>
            <hr>
            <b>View Storage</b><br>
            <a href=/reports/listStorage/2011>2011: List Storage for All Players </a><br>
            <hr>
            <b>View Players and Characters</b><br>
            <a href=/reports/listPlayersCharacters>Show all players and their characters</a><br>
            <hr>
            <b>View Characters and their levels</b><br>
            <a href=/reports/listCharactersLevels>Levels</a><br>
            <hr>
            <b>Show faction list</b><br>
            <a href=/reports/listFactions>Show factional breakdown</a><br>
            <hr>
            <b>Show class breakdown list</b><br>
            <a href=/reports/listClasses>Show class breakdown</a><br>
            <hr>
            <b>List Player Lives</b><br>
            <a href=/reports/listLives>Gift/Godsend Report</a><br>
            <hr>
        </div>
    </div>
        <div id="fragment-3">
        <div style="width:100%; font-size:12px;">
            <hr>
            <b>Show all Transactions</b><br>
            <a href=/reports/showAllSkus>Show all Transactions</a><br>
        </div>
    </div>
    <div id="fragment-4">
        <div style="width:100%; font-size:12px;">
            <b>Vocation Report</b><br>
            <a href=/reports/vocationReport>Vocation Report</a><br>
            <hr>
        </div>
    </div>
</div>
