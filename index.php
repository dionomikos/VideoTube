<?php require_once("includes/header.php"); ?>


<?php
// session_destroy(); this resets the whole session
// session_unset($_SESSION["userLoggedIn"]); This will unset only this variable

if(isset($_SESSION["userLoggedIn"])) {
    echo "user is logged in as " . $_SESSION["userLoggedIn"];
}
else {
    echo "not logged in";
}
?>


<?php require_once("includes/footer.php"); ?>
                