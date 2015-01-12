<?php
use WebDreamt\Box;
use WebDreamt\Component;
use WebDreamt\Component\Wrapper;
use WebDreamt\Component\Wrapper\Data\Form;
use WebDreamt\Component\Wrapper\Group\Table;
use WebDreamt\Component\Wrapper\Panel;
use WebDreamt\Component\Wrapper\Select;
use WebDreamt\Server;
//This may be included from somewhere else.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	require_once __DIR__ . '/../vendor/autoload.php';
}
$error = "";
$box = Box::get();
if (!$box) {
	echo "Could not find a Box! <br>";
	return;
}

$sentry = $box->sentry();
$server = $box->server();

if (isset($_POST['1-name'])) {
	$sentry->createGroup(['name' => $_POST['1-name']]);
}

$groups = $sentry->findAllGroups();
$tables = $box->db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

$options = [
	0 => 'None',
	1 => 'Delete',
	2 => 'Update',
	4 => 'Create',
	3 => 'Up. & Del.',
	5 => 'Cr. & Del.',
	6 => 'Cr. & Up.',
	7 => 'All'
];

$table = new Table(null, 'table-bordered');
$tableWrapper = new Wrapper($table);
$select = new Select($options);
$panel = new Panel($tableWrapper, null, 'style="margin: 40px auto 0 auto;"');
$panel->setTitle('Group Permissions');
$groupNames = [];
foreach ($groups as $group) {
	$groupNames[] = $group['name'];
}
$table->setHeaders(array_merge(['Tables'], $groupNames));
$table->setCellComponent(new Wrapper($select, 'td'));
$table->getRowComponent()->setUseFirst(true)->setFirstComponent(new Component('td'));
$tableWrapper->setOnNullInput('There are no groups.');
$data = [];
$count = 0;
if (!empty($groups)) {
	foreach ($tables as $table) {
		$data[] = [$table];
		foreach ($groups as $group) {
			$create = $server->permissionsContain($group['permissions'], $table, Server::ACT_CREATE);
			$update = $server->permissionsContain($group['permissions'], $table, Server::ACT_UPDATE);
			$delete = $server->permissionsContain($group['permissions'], $table, Server::ACT_DELETE);

			$value = 0;
			$value += $create ? 4 : 0;
			$value += $update ? 2 : 0;
			$value += $delete ? 1 : 0;

			$data[$count][] = $value;
		}
		$count++;
	}
} else {
	$data = null;
}

$form = new Form('groups');
$form->setHtml("method='POST'")->deny('permissions')->setHtmlType(['name' => Form::HTML_TEXT]);
$formPanel = new Panel($form);
$formPanel->setTitle('Add Group');

echo $box->header();
echo $formPanel->render();
echo $panel->render($data);
echo $box->footer();
