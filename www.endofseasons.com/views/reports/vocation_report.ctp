<h1>Vocation Report</h1>
    <table align='left'><tr><th>Character Name</th><th>Vocations</th></tr>

<?php
    $count = 0;
    foreach ($characters as $character) {
        echo "<tr><td>";
        echo $character['Character']['name'];
        echo "</td><td>";
        $rule = $rules[$count];
        foreach ($rule as $r) {
            echo $r['Rule']['name']."<br>";
        }
        echo "</td></tr>";
        ++$count;
    }
?>
    </table>
