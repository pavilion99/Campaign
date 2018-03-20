<?php
require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("endorsements.pending", "");

$sql = DB::get();

$endorsements = [];

$uendorsements = Endorsement::get_all();
foreach ($uendorsements as $endorsement) {
	/** @var Endorsement $endorsement */
	if ($endorsement->isActive() && !$endorsement->isIgnored())
		$endorsements[] = $endorsement;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php Campaign::head(); ?>
	</head>
	<body>
		<?php Campaign::nav(); ?>
		<div class="container-fluid h-100">
			<div class="row" id="main-content">
				<?php Campaign::sidebar(); ?>
				<div class="col-lg-9 px-5 py-4">
					<h1 class="mb-4">Active Endorsements</h1>
					<?php Campaign::message(); ?>
					<div class="table-responsive">
						<table class="table">
							<tr>
								<th>
									Title
								</th>
								<th>
									Submitter
								</th>
								<th>
									Content
								</th>
								<?php if (Campaign::check_permission("endorsements.resolve")): ?>
									<th class="text-center">
										Ignore
									</th>
								<?php endif; ?>
							</tr>
							<?php if (empty($endorsements)): ?>
								<tr>
									<td colspan="100">
										<b>No active endorsements found.</b>
									</td>
								</tr>
							<?php endif; ?>
							<?php foreach ($endorsements as $endorsement): ?>
								<?php /** @var Endorsement $endorsement */ ?>
								<tr>
									<td><?= $endorsement->getTitle() ?></td>
									<td><?= $endorsement->getSender() ?></td>
									<td><?= $endorsement->getContent() ?></td>
									<?php if (Campaign::check_permission("endorsements.resolve")): ?>
										<td class="text-center">
											<a href="../resolve/?id=<?= $endorsement->getID() ?>&resolution=ignore"><span
													class="fa fa-thumbs-down"></span></a>
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
