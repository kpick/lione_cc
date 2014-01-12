<h1><strong>Factional Breakdown</strong></h1>
<?php
    $factionlist = array("Chosen" ,"Realm of Seasons", "Artificer", "NONE");
    $total_count = count($player_names);
    foreach ($factionlist as $faction) {
        $count = 0;
        echo $faction;
        echo "<table align='left'><tr><th>Player Name</th><th>Character</th></tr>";
        for($i = 0; $i < count($player_names); $i++) {
            if($factions[$i] == $faction){
                echo "<tr><td>";
                echo $player_names[$i];
                echo "</td><td>";
                echo "<a href=/characters/view/$ids[$i]>$characters[$i]</a>";
                echo "</td><td>";
                ++$count;
            }
        } // end foreach
        $percent = (float) ($count/$total_count)*100.0;
        echo "<tr><td>TOTAL: </td><td>$count of $total_count ($percent%)</td></tr>";
        echo "</table>";
        echo "<hr>";
    }
?>