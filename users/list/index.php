<?php
require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("users.list", "");

$sql = DB::get();

$users = [];

$usersQL = $sql->query("SELECT * FROM `users` ORDER BY `id` ASC");

if ($sql->errno) {
	throw new CampaignException("Error during database query. MySQL said: ".$sql->error.". Contact your Tech Chair with this information.", "");
}

if ($row = $usersQL->fetch_assoc()) {
	do {
		$users[] = $row;
	} while($row = $usersQL->fetch_assoc());
} else {
	throw new CampaignException("The database was successfully connected, but there was an error retrieving data. MySQL said: ".$sql->error.". Contact the tech chair with this information.", "");
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php Campaign::head(); ?>
		<script>
			function modalSet(id, name) {
			    document.querySelector("#userNameDeleteConfirm").innerHTML = name;
				document.querySelector("#idDeleteConfirm").href = "../delete/?id=" + id;
			}
		</script>
	</head>
	<body>
		<div class="modal fade" id="deleteConfirm" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="deleteConfirmLabel">Confirm</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Are you sure that you want to delete the user "<span id="userNameDeleteConfirm"></span>"?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
						<a id="idDeleteConfirm" href="javascript:void(0);" class="btn btn-outline-danger">Delete User</a>
					</div>
				</div>
			</div>
		</div>
		<?php Campaign::nav(); ?>
		<div class="container-fluid h-100">
			<div class="row" id="main-content">
				<?php Campaign::sidebar(); ?>
				<div class="col-lg-9 px-5 py-4">
					<h1 class="mb-4">All Users</h1>
					<?php Campaign::message(); ?>
					<div class="table-responsive">
						<table class="table">
							<tr>
								<th>
									Name
								</th>
								<th>
									Username
								</th>
								<th>
									Position
								</th>
                                <?php if (Campaign::check_permission("users.edit")): ?>
									<th class="text-center">
										Edit
									</th>
                                <?php endif; ?>
                                <?php if (Campaign::check_permission("users.delete")): ?>
									<th class="text-center">
										Delete
									</th>
                                <?php endif; ?>
							</tr>
                            <?php if (empty($users)): ?>
								<tr>
									<td colspan="100">
										<b>No users found.</b>
									</td>
								</tr>
                            <?php endif; ?>
                            <?php foreach ($users as $user): ?>
								<tr>
									<td><?= $user["firstname"]." ".$user["lastname"] ?></td>
									<td><?= $user["username"] ?></td>
									<td><?= $user["position"] ?></td>
                                    <?php if (Campaign::check_permission("users.edit")): ?>
										<td class="text-center">
											<a href="../edit/?id=<?= $user["id"] ?>"><span
														class="fa fa-pencil-square"></span></a>
										</td>
                                    <?php endif; ?>
                                    <?php if (Campaign::check_permission("users.delete")): ?>
										<td class="text-center">
											<a data-toggle="modal" data-target="#deleteConfirm"
											   href="javascript:void(0);"
											   onclick="modalSet(<?= $user["id"] ?>, '<?= $user["username"] ?>');"><span
														class="fa fa-trash"></span></a>
										</td>
                                    <?php endif; ?>
								</tr>
                            <?php endforeach; ?>
						</table>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
