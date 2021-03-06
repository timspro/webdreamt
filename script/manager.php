<?php
use WebDreamt\Box;
use WebDreamt\Component\Wrapper\Select;

if (php_sapi_name() !== 'cli' && __FILE__ === $_SERVER["SCRIPT_FILENAME"]) {
	echo 'As a security precaution, this file cannot be run directly and instead '
	. 'must be included from somewhere else.';
	return;
}

$box = Box::get();
if (empty($box->DatabaseName)) {
	echo "The box at the very least must be set to use a certain database name.";
	return;
}
$box->enable();

$data = [
	"update-database" => "Update DB from Propel",
	"update-propel" => "Update Propel from DB",
	"build-all" => "Basic Configuration",
	"add-schemas" => "Add in Sentry",
	"fill-database" => "Fill Database with Data",
	"delete-database" => "Delete Data from Database",
	"destroy-database" => "Delete Tables from Database"
];

$select = new Select($data, null, 'id="option" name="script"');
$error = "";
if (isset($_GET['script']) || isset($argv[1])) {
	if (isset($argv[1]) && ($argv[1] === '--help' || $argv[1] === '-h')) {
		echo 'Options are: update-database, update-propel, add-schemas (for authorization), fill-database, ' .
		'delete-database (data), and destroy-database (schemas)';
		return;
	}
	$value = $_GET['script'] ? : $argv[1];
	try {
		echo "Trying to: " . $value . "<br>";
		if ($value === "build-all") {
			$box->builder()->build();
		} else if ($value === "update-database") {
			$box->builder()->updateDatabase();
		} else if ($value === "update-propel") {
			$box->builder()->updatePropel();
		} else if ($value === "add-schemas") {
			$box->builder()->addSchemas();
		} else if ($value === "fill-database") {
			$box->filler()->addData();
		} else if ($value === "delete-database") {
			$box->builder()->deleteData();
		} else if ($value === "destroy-database") {
			$box->builder()->deleteDatabase();
		} else {
			$message = "Unknown command. ";
			if (php_sapi_name() === 'cli') {
				$message .= "Use --help to see a list of command if you are using a command line.";
			}
			throw new Exception($message);
		}
		$error = "Completed successfully.";
	} catch (Exception $err) {
		$error = $err->getMessage();
	}
	if (isset($argv[1])) {
		echo $error;
		return;
	}
}
echo $box->header();
?>
<div><?= $error ?></div>
<div class="panel panel-default" style="max-width: 500px; margin: 40px auto 0 auto; padding: 15px">
	<form method="get" role="form">
		<div class="form-group">
			<label for="option">Option: </label>
			<?= $select->render(isset($_GET['script']) ? $_GET['script'] : null) ?>
		</div>
		<button class="btn btn-default" type="submit">Submit</button>
	</form>
</div>
<?php
echo $box->footer();
