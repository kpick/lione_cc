<h1>ALL PLAYERS</h1>
<?php
    $total = 0;
    echo "<table align='left'><tr><th>Player Name</th><th>Kitchen Cash</th></tr>";
    for($i = 0; $i < count($view_players); $i++) {
          echo "<tr><td>";
          echo $view_players[$i]['first_name']." ".$view_players[$i]['last_name'];
          echo "</td><td>";
          echo $view_players[$i]['kitchen_cash'];
          echo "</td></tr>";
          $total += $view_players[$i]['kitchen_cash'];
    } // end foreach
    echo "<tr></tr>";
    echo "<tr><td>TOTAL</td><td>$total</td></tr>";
    echo "</table>";
?>