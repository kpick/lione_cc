<h1>LEVELS</h1>
<?php

    echo "<table align='left'><tr><th>Player Name</th><th>Characters</th><th>Most Recent Event Count</th></tr>";
    for($i = 0; $i < count($view_players); $i++) {
        // Make Link
        $char_id =  Set::extract('/Character/id',$view_characters[$i]);
        $char_id = $char_id[0];
        $link = "<a href=/characters/view/".$char_id.">";
        $name = Set::extract('/Character/name',$view_characters[$i]);
        $charname = $name[0];
        $charlink = $link.$charname."</a>";

        // Show level
        $level =  Set::extract( '/Character/level', $view_characters[$i]);
        $level = $level[0];

        echo "<tr><td>";
        echo $view_players[$i];
        echo "</td><td>";
        echo $charlink;
        echo "</td><td>";
        echo $level;
        echo "</td></tr>";
    } // end foreach
    echo "</table>";

?>