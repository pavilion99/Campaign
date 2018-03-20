<?php require_once(__DIR__."/Config.php"); ?>
<div class="col-lg-3" id="sidebar">
	<div id="sidebar-container" style="margin-left: -15px; margin-right: -15px">
		<a href="<?= Config::get("app-root")."contacts/list" ?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-list"></span>&nbsp;List all Contacts
		</a>
		<a href="<?= Config::get("app-root")."contacts/register" ?>" class="w-100 py-3 rounded-0 text-primary text-center">
			<span class="fa fa-pencil"></span>&nbsp;Register a New Contact
		</a>
	</div>
</div>