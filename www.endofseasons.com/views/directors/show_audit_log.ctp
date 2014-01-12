<h1>AUDIT LOGS</h1>

<table align='left'><tr>
        <th>ID</th>
        <th>Player ID</th>
        <th>Director ID</th>
        <th>Token Type</th>
        <th>CP Added</th>
        <th>Description</th>
        <th>Modified</th>
        <th>Created</th>
    </tr>
<?php
    for($i = 0; $i < count($audit); $i++) {
          echo "<tr><td>";
          echo $audit[$i]['id'];
          echo "</td><td>";
          echo $audit[$i]['player_id'];
          echo "</td><td>";
          echo $audit[$i]['director_id'];
          echo "</td><td>";
          echo $audit[$i]['token_type'];
          echo "</td><td>";
          echo $audit[$i]['cp_added'];
          echo "</td><td>";
          echo $audit[$i]['description'];
          echo "</td><td>";
          echo $audit[$i]['modified'];
          echo "</td><td>";
          echo $audit[$i]['created'];
          echo "</td></tr>";
    }
?>
</table>
