<?php
if (isset($_SESSION["message"]["content"])):
$type = $_SESSION["message"]["type"] ?? "default";
    ?>
<div class="alert alert-<?= $type ?>"><?= $_SESSION["message"]["content"] ?></div>
<?php unset($_SESSION["message"]["content"]); unset($_SESSION["message"]["type"]); endif;