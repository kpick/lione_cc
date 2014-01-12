<h1>STORAGE: <?= $year ?></h1>
<?php
    echo "<table align='left'><tr><th>Player Name</th><th>Storage</th><th>Quantity</th></tr>";
    for($i = 0; $i < count($view_players); $i++) {
          echo "<tr><td>";
          echo $view_players[$i];
          echo "</td><td>";
          echo $view_skus[$i];
          echo "</td><td>";
          echo $view_quant[$i];
          echo "</td></tr>";
    } // end foreach
    echo "</table>";
?>