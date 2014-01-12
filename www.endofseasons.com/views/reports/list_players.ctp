<h1>ALL PLAYERS</h1>
<?php
    echo "<table align='left'><tr><th>Player Name</th><th>email</th><th>Address</th><th>Phone</th>
        <th>Emergency Contact</th><th>Date of Birth</th><th>Membership Expiration</th>
        <th>Event Tokens (3 Day)</th><th>Food Tokens (3 day)</th><th>MINOR</th>
        <th>Allergies</th></tr>";
    for($i = 0; $i < count($view_players); $i++) {
          echo "<tr><td>";
          echo $view_players[$i]['first_name']." ".$view_players[$i]['last_name'];
          echo "</td><td>";
          if($view_players[$i]['minor']){ echo "<b>"; }
          echo $view_players[$i]['email'];
          if($view_players[$i]['minor']){ echo "</b>"; }
          echo "</td><td>";
          echo $view_players[$i]['address']." ".$view_players[$i]['city']." ".$view_players[$i]['state']." ".$view_players[$i]['zipcode'];
          echo "</td><td>";
          echo $view_players[$i]['phone'];
          echo "</td><td>";
          echo $view_players[$i]['emergency_contact'];
          echo "</td><td>";
          echo $view_players[$i]['dob'];
          echo "</td><td>";
          echo $view_players[$i]['member_until'];
          echo "</td><td>";
          echo $view_players[$i]['event_token_3_day'];
          echo "</td><td>";
          echo $view_players[$i]['food_token_3_day'];
          echo "</td><td>";
          echo $view_players[$i]['minor'];
          echo "</td><td>";
          echo $view_players[$i]['allergies'];
          echo "</td></tr>";
    } // end foreach
    echo "</table>";
?>