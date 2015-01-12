<?php
use WebDreamt\Box;
use WebDreamt\Component\Wrapper\Select;
//This may be included from somewhere else.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	require_once __DIR__ . '/../vendor/autoload.php';
}
$error = "";
$box = Box::get();
if (!$box) {
	$box = new Box(false);
	echo "Could not find a Box! <br>";
	return;
}

$data = [
	"update-database" => "Update DB from Propel",
	"update-propel" => "Update Propel from DB",
	"build-all" => "Basic Configuration",
	"add-schemas" => "Add in Sentry",
	"fill-database" => "Fill Database with Data",
	"delete-database" => "Delete Data from Database",
	"destroy-database" => "Delete Tables from Database"
];

$select = new Select($data, false, null, 'id="option" name="script"');
if (isset($_GET['script']) || isset($argv[1])) {
	if (isset($argv[1]) && ($argv[1] === '--help' || $argv[1] === '-h')) {
		echo 'Options are: update-database, update-propel, add-schemas (for authorization), fill-database, ' .
		'delete-database (data), and destroy-database (schemas)';
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
			throw new Exception("Unknown command. Use --help to see a list of command if you are "
			. "using a command line.");
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
