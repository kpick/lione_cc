<h1>Class Breakdown</h1>
<?php
    $classlist = array("Crusader", "Combat Tech", "Hunter", "Nightblade",
        "Ritualist", "Runic Guardian", "Preserver", "Weapon Master",
        "Warden", "Scourge", "Spellsword", "Magus", "NONE");

    $total_count = count($player_names);
    foreach ($classlist as $class) {
        $count = 0;
        echo $class;
        echo "<table align='left'><tr><th>Player Name</th><th>Character</th></tr>";
        for($i = 0; $i < count($player_names); $i++) {
            if($classes[$i] == $class){
                echo "<tr><td>";
                echo $player_names[$i];
                echo "</td><td>";
                echo "<a href=/characters/view/$ids[$i]>$characters[$i]</a>";
                echo "</td></tr>";
                ++$count;
            }
        } // end foreach
        $percent = (float) ($count/$total_count)*100.0;
        echo "<tr><td>TOTAL: </td><td>$count of $total_count ($percent%)</td></tr>";
        echo "</table>";
        echo "<hr>";
    }
?>