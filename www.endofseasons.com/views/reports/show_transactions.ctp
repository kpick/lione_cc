<?php

    $event_total = $num_event_tokens * $event_cost;
    $food_total = $num_food_tokens * $food_cost;

    echo "<table align='left'><tr><th>Token Type</th><th>Token Number</th><th>Cost</th><th>Total</th></tr>";
    echo "<tr><td>Event</td><td>";
    echo $num_event_tokens;
    echo "</td><td>";
    echo '$'.$event_cost;
    echo "</td><td>";
    echo '$'.$event_total;
    echo "</td></tr>";

    echo "<tr><td>Food</td><td>";
    echo $num_food_tokens;
    echo "</td><td>";
    echo '$'.$food_cost;
    echo "</td><td>";
    echo '$'.$food_total;
    echo "</td></tr>";

    echo "<tr><td>Total</td>";
    echo "<td/><td/><td/>";
    echo '$'.($event_total + $food_total);
    echo "</td></tr>";
    echo "</table>";

?>