<?php require_once(__DIR__."/Config.php"); ?>
<div class="col-lg-3" id="sidebar">
	<div id="sidebar-container" style="margin-left: -15px; margin-right: -15px">
		<a href="<?= Config::get("app-root")."endorsements/pending" ?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-list-ul"></span>&nbsp;List Pending Endorsements
		</a>
		<a href="<?= Config::get("app-root")."endorsements/active" ?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-list-ul"></span>&nbsp;List Active Endorsements
		</a>
	</div>
</div>