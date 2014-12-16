<?php
use WebDreamt\Box;
use WebDreamt\Hyper\Select;
require_once __DIR__ . '/bootstrap.php';
$box = new Box(false);
$select = new Select();
$directory = __DIR__ . '/src/WebDreamt/output/';
$forms = array_diff(scandir($directory, SCANDIR_SORT_ASCENDING), array('..', '.'));
if (isset($_GET['script'])) {
	$select->setSelected($_GET['script']);
}
$select->appendHtml('name="script"');
$output = $select->render($forms);
echo $box->header();
?>
<div class="panel panel-default" style="max-width: 500px; margin: 40px auto 0 auto; padding: 15px">
	<form method="get" role="form">
		<div class="form-group">
			<label for="option">HTML: </label>
			<?= $output ?>
		</div>
		<button class="btn btn-default" type="submit">Submit</button>
	</form>
</div>
<?php
if (isset($_GET['script'])) {
	?>
	<div class="panel panel-default" style="padding: 20px; margin-top: 50px">
		<?php
		require $directory . $_GET['script'];
		?>
	</div>
	<?php
}
?>
<?php
echo $box->footer();
