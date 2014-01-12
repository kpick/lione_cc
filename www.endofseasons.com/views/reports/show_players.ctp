<?php
    if (isset($javascript)) {
        echo $javascript->link("js/prototype.js");
        echo $javascript->link("js/scriptaculous.js");
    }
?>
    <table align='left'><thead><tr><th>ID</th><th>Player Name</th><th>Character Name</th><th>Food</th></tr></thead>
    <tbody id="sort_table">
<?php

    //make display array.
    $display = array();
    for($i = 0; $i < count($view_player_name); $i++) {
        $row = array("id" => $player_ids[$i],
            "name"=>$view_player_name[$i],
            "char_name"=>$view_char_names[$i],
            "food"=>$view_food[$i]);
        array_push($display, $row);
    }

    $display = Set::sort($display, '{n}.name', 'asc');
    $count = 0;
    for($i = 0; $i < count($display); $i++) {
          echo "<tr><td>";
          echo $display[$i]['id'];
          echo "</td><td>";
          echo $display[$i]['name'];
          echo "</td><td>";
          echo $display[$i]['char_name'];
          echo "</td><td>";
          if ($display[$i]['food'] == 0) {
              echo "no";
          } else {
              echo "yes";
              ++$count;
          }
          echo "</td></tr>";
    } // end foreach
    echo "</tbody><tr><td><b>TOTAL</b></td><td><b>PLAYERS: $i</b></td><td><b>FOOD: $count</b></td></tr>";
?>
    </table>