<?php require_once(__DIR__."/Config.php"); ?>
<div class="col-lg-3" id="sidebar">
	<div id="sidebar-container" style="margin-left: -15px; margin-right: -15px">
		<a href="<?= Config::get("app-root")."users/list" ?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-list"></span>&nbsp;Show All Users
		</a>
		<a href="<?= Config::get("app-root")."users/provision" ?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-pencil"></span>&nbsp;Provision a New User
		</a>
	</div>
</div>