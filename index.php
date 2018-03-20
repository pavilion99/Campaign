<?php
require_once("assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();

$sql = DB::get();

$user = $sql->query("SELECT * FROM `users` WHERE `id`=".$_SESSION["id"])->fetch_assoc();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php Campaign::head(); ?>
	</head>
	<body>
		<?php Campaign::nav(); ?>
		<div class="container-fluid h-100 px-3 py-3">
			<h1 class="text-center">Welcome, <?= $user["firstname"] ?></h1>
			<h3 class="text-center text-muted mb-4"><?= date_diff(new DateTime(), new DateTime(Config::get("election-date")), true)->format("%a") ?> days until the election.</h3>
			<?php Campaign::message(); ?>
			<div class="card-columns">
				<?php if (Campaign::check_permission("alerts.send")): ?>
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">
							Send an Alert
						</h5>
						<p class="card-text">Write a new alert that will be delivered almost instantly via text to all of your contacts.</p>
						<a href="alerts/send" class="btn btn-primary">Go</a>
					</div>
				</div>
				<?php endif; ?>
				<?php if (Campaign::check_permission("alerts.list")): ?>
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">
							View Alert History
						</h5>
						<p class="card-text">View all alerts that have been sent during this campaign and choose to resend them.</p>
						<a href="alerts/history" class="btn btn-primary">Go</a>
					</div>
				</div>
				<?php endif; ?>
				<?php if (Campaign::check_permission("contacts.list")): ?>
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">
							See All Contacts
						</h5>
						<p class="card-text">View a table containing all of the contacts that have registered with your campaign.</p>
						<a href="contacts/list" class="btn btn-primary">Go</a>
					</div>
				</div>
				<?php endif; ?>
				<?php if (Campaign::check_permission("contacts.register")): ?>
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">
								Register a Contact
							</h5>
							<p class="card-text">Register a new person with the campaign, allowing them to stay in touch with us.</p>
							<a href="contacts/register" class="btn btn-primary">Go</a>
						</div>
					</div>
				<?php endif; ?>
				<?php if (Campaign::check_permission("users.create")): ?>
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">
								Provision a User
							</h5>
							<p class="card-text">Provision a new user for the campaign, enabling them to log in to this site.</p>
							<a href="users/provision" class="btn btn-primary">Go</a>
						</div>
					</div>
				<?php endif; ?>
				<?php if (Campaign::check_permission("interactions.create")): ?>
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">
								Create an Event
							</h5>
							<p class="card-text">Create a new GOTV Event (meeting, round-table, etc.)</p>
							<a href="gotv/events/create" class="btn btn-primary">Go</a>
						</div>
					</div>
				<?php endif; ?>
				<?php if (Campaign::check_permission("interactions.list")): ?>
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">
								Show All Events
							</h5>
							<p class="card-text">Show all past GOTV Events (meetings, round-tables, etc.)</p>
							<a href="gotv/events/list" class="btn btn-primary">Go</a>
						</div>
					</div>
				<?php endif; ?>
				<?php if (Campaign::check_permission("users.list")): ?>
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">
								List All Users
							</h5>
							<p class="card-text">View all the users that are currently permitted to log in to this campaign.</p>
							<a href="users/list" class="btn btn-primary">Go</a>
						</div>
					</div>
				<?php endif; ?>
				<?php if (Campaign::check_permission("endorsements.pending")): ?>
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">
								Show Pending Endorsements
							</h5>
							<p class="card-text">View all endorsements that have been submitted but have not yet been approved by the campaign.</p>
							<a href="endorsements/pending" class="btn btn-primary">Go</a>
						</div>
					</div>
				<?php endif; ?>
				<?php if (Campaign::check_permission("users.list")): ?>
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">
								Show Active Endorsements
							</h5>
							<p class="card-text">Show a list of all of the approved endorsements that current show on the user-facing website.</p>
							<a href="endorsements/active" class="btn btn-primary">Go</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</body>
</html>
