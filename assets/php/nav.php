<?php require_once(__DIR__."/Config.php"); ?>
<?php $active = strtolower(explode('/', $_SERVER["REQUEST_URI"])[2]); ?>
<nav class="navbar fixed-top navbar-light bg-light navbar-expand-lg">
	<a class="navbar-brand" href="<?= Config::get("app-root") ?>"><?= Config::get("campaign-name") ?></a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item <?= $active == "alerts" ? "active" : ""  ?>">
				<a class="nav-link" href="<?= Config::get("app-root") ?>alerts">Alerts</a>
			</li>
			<li class="nav-item <?= $active == "contacts" ? "active" : ""  ?>">
				<a class="nav-link" href="<?= Config::get("app-root") ?>contacts">Contacts</a>
			</li>
			<li class="nav-item <?= $active == "users" ? "active" : ""  ?>">
				<a class="nav-link" href="<?= Config::get("app-root") ?>users">Users</a>
			</li>
			<li class="nav-item <?= $active == "endorsements" ? "active" : ""  ?>">
				<a class="nav-link" href="<?= Config::get("app-root") ?>endorsements">Endorsements</a>
			</li>
			<li class="nav-item <?= $active == "gotv" ? "active" : ""  ?> dropdown">
				<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">GOTV</a>
				<div class="dropdown-menu">
					<a class="dropdown-item" href="<?= Config::get("app-root") ?>gotv/events">Events</a>
					<a class="dropdown-item" href="<?= Config::get("app-root") ?>gotv/people">People</a>
				</div>
			</li>
		</ul>
		<ul class="navbar-nav">
			<li class="nav-item">
				<a class="nav-link" href="<?= Config::get("app-root")."logout" ?>">Logout</a>
			</li>
		</ul>
	</div>
</nav>