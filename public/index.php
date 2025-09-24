<?php
 function rollDice() {
    return rand(1, 6);
 }

 if (isset($_POST['roll'])) {
    $result = rollDice();
    echo "you rolled a $result";

    if ($result == 6) {
        echo "<br>\u{1F389}congrats!!";
    }
 }



?>

<form method="POST">
    <input type="submit" name="roll"  value="toss">
</form>