<?php
header("Access-Control-Allow-Origin: https://spencer-colton.squarespace.com");
require_once("../../assets/php/Campaign.php");
require_once("../../assets/php/Endorsement.php");
$endorsements = Endorsement::get_all();
?>
<html>
	<head>
		<link rel="stylesheet" href="<?= Config::get("app-root") ?>assets/css/endorsements.css">
		<script>
			function ld() {
				window.parent.postMessage(document.getElementById("content").offsetHeight,"*");
			}

			window.addEventListener(
				"load",
				ld
			);
		</script>
	</head>
	<body>
		<div class="container" id="content">
			<?php foreach ($endorsements as $endorsement):
				/** @var Endorsement $endorsement */
				if (!$endorsement->isActive() || $endorsement->isIgnored())
					continue;

				$col = $endorsement->getID() % 4;
				$color = "";
				$text = "#000;";

				switch ($col) {
					case 0:
						$col = "#e71d36";
						break;
					case 1:
						$col = "#1b998b";
						break;
					case 2:
						$col = "#ffcd07";
						break;
					case 3:
						$col = "#f46036";
						break;
					default:
						$col = "#fff";
						break;
				}
			?>
				<div class="endorsement" style="background-color: <?= $col ?>;">
					<span class="endorsement-title" style="color: <?= $text ?>"><?= $endorsement->getTitle() ?></span>
					<blockquote class="endorsement-body" style="color: <?= $text ?>"><?= $endorsement->getContent() ?></blockquote>
					<span class="endorsement-sender" style="color: <?= $text ?>">- <?= $endorsement->getSender() ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</body>
</html>