<?php require_once(__DIR__."/Config.php"); ?>
<div class="col-lg-3" id="sidebar">
	<div id="sidebar-container" style="margin-left: -15px; margin-right: -15px">
		<a href="<?= Config::get("app-root")."alerts/send" ?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-pencil"></span>&nbsp;Send a New Alert
		</a>
		<a href="<?= Config::get("app-root")."alerts/history" ?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-history"></span>&nbsp;View Alert History
		</a>
		<a href="<?= Config::get("app-root")."alerts/queue"?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-list"></span>&nbsp;Check Alert Queue
		</a>
	</div>
</div>