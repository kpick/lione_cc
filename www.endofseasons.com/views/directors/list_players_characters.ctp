<h1>ALL PLAYERS</h1>
<?php

    echo "<table align='left'><tr><th>Player Name</th><th>Characters</th></tr>";
    for($i = 0; $i < count($view_players); $i++) {
          echo "<tr><td>";
          echo $view_players[$i];
          echo "</td><td>";
          echo $view_characters[$i];
          echo "</td></tr>";
    } // end foreach
    echo "</table>";

?>