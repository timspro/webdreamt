<?php
use WebDreamt\Box;
use WebDreamt\Extra\Select;
require_once __DIR__ . '/../../../vendor/autoload.php';
$error = "";
$box = new Box(false);
if (isset($_GET['script'])) {
	$select = new Select($_GET['script']);
	try {
		echo "Trying to: " . $_GET['script'] . "<br>";
		if ($_GET['script'] === "build-all") {
			$box->builder()->build();
		} else if ($_GET['script'] === "update-database") {
			$box->builder()->updateDatabase();
		} else if ($_GET['script'] === "update-propel") {
			$box->builder()->updatePropel();
		} else if ($_GET['script'] === "add-schemas") {
			$box->builder()->addSchemas();
		} else if ($_GET['script'] === "fill-database") {
			$okay = 0;
			$data = require_once __DIR__ . '/data/amount.php';
			$custom = require_once __DIR__ . '/data/custom.php';
			while ($okay < 10) {
				try {
					$box->filler()->addData($data, false, $custom);
					$okay = 100;
				} catch (Exception $e) {
					$okay++;
					if ($okay === 5) {
						echo $e->getFile() . " " . $e->getLine() . " " . $e->getMessage() . "<br>";
						echo $e->getTraceAsString();
					} else {
						echo $e->getMessage() . " - Retrying... <br>";
					}
				}
			}
		} else if ($_GET['script'] === "delete-database") {
			$box->builder()->deleteData();
		} else if ($_GET['script'] === "destroy-database") {
			$box->builder()->deleteDatabase();
		}
		$error = "Completed successfully.";
	} catch (Exception $err) {
		$error = $err->getMessage();
	}
} else {
	$select = new Select();
}
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Septic360</title>
    <link href="<?= $box->root() ?>/css/bootstrap.css" rel="stylesheet">
  </head>
	<body style="background-color: #e3e3e3; padding: 50px;">
		<div><?= $error ?></div>
		<div class="panel panel-default" style="max-width: 500px; margin: 20px auto 0 auto; padding: 15px">
			<form method="get" role="form">
				<div class="form-group">
					<label for="option">Option: </label>
					<select id="option" name="script" class="form-control">
						<option <?= $select->select("update-database") ?>>Update DB from Propel</option>
						<option <?= $select->select("update-propel") ?>>Update Propel from DB</option>
						<option <?= $select->select("build-all") ?>>Basic Configuration</option>
						<option <?= $select->select("add-schemas") ?>>Add in Sentry</option>
						<option <?= $select->select("fill-database") ?>>Fill Database with Data</option>
						<option <?= $select->select("delete-database") ?>>Delete Data from Database</option>
						<option <?= $select->select("destroy-database") ?>>Delete Tables from Database</option>
					</select>
				</div>
				<button class="btn btn-default" type="submit">Submit</button>
			</form>
		</div>
	</body>
</html>