<h1>TRANSACTIONS</h1>
<?php

    echo "<table align='left'><tr><th>Transaction ID</th><th>Date</th><th>Player Name</th><th>Skus</th></tr>";
    foreach($transactions as $transaction) {
        $t = Set::extract('/Transaction/.', $transaction);
        $player = Set::extract('/Player/.', $transaction);
        $name = $player[0]['first_name'].' '.$player[0]['last_name'];
        $skus = Set::extract('/Skus/.', $transaction);

        echo "<tr><td>";
        echo $t[0]['id'];
        echo "</td><td>";
        echo $t[0]['created'];
        echo "</td><td>";
        echo $name;
        echo "</td><td>";
        foreach($skus as $sku) {
            echo $sku['description'];
            echo ': ';
            echo $sku['base_cost'];
            echo "<br>";
        }
        echo "</td></tr>";
    } // end foreach
    echo "</table>";

?>