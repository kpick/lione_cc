<h1>ALL PLAYERS</h1>
<?php

    echo "<table align='left'><tr><th>Player Name</th><th>Characters</th>
        <th>Gifts</th><th>Godsends</th></tr>";
    for($i = 0; $i < count($players); $i++) {
          echo "<tr><td>";
          echo $players[$i];
          echo "</td><td>";
          echo $characters[$i];
          echo "</td><td>";
          echo $gifts[$i];
          echo "</td><td>";
          echo $godsends[$i];
          echo "</td></tr>";
    } // end foreach
    echo "</table>";

?>