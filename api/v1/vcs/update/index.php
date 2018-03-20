<?php
require_once("../../../../assets/php/Campaign.php");
$app = Config::get("app-root-internal");

$str = [];
$code = -1;
shell_exec("cd $app && git config credential.helper 'store --file=/home/senu_tech/.git-credentials'");
exec("cd $app && /usr/bin/git add .");
exec("cd $app && /usr/bin/git stash");
exec("cd $app && /usr/bin/git pull 2>&1", $str, $code);

foreach ($str as $st) {
	echo $st."<br>";
}

sleep(1);

if ($code == 0) {

	$msg = json_encode([
		"text" => "The website has been updated with the last commit."
	]);
	Slack::hook("slack-vcs-hook-url", $msg);
} else {
	$line = "";

	foreach ($str as $st) {
		$line .= $st."\n";
	}

	$msg = json_encode([
		"text" => "Something went wrong updating the site with that last commit...",
		"attachments" => [
			[
				"title" => "Error in `git pull`",
				"text" => $line,
				"mrkdwn_in" => ["text"]
			]
		]
	]);

	Slack::hook("slack-vcs-hook-url", $msg);
}
