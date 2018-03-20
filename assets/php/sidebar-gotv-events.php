<?php require_once(__DIR__."/Config.php"); ?>
<div class="col-lg-3" id="sidebar">
	<div id="sidebar-container" style="margin-left: -15px; margin-right: -15px">
		<a href="<?= Config::get("app-root")."gotv/events/create" ?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-pencil"></span>&nbsp;Create a New Event
		</a>
		<a href="<?= Config::get("app-root")."gotv/events/list" ?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-history"></span>&nbsp;List All Events
		</a>
	</div>
</div>