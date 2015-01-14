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

//Check that the box is setup.
$box = Box::get();
if (!$box) {
	echo "Could not find a Box! <br>";
	return;
}

$sentry = $box->sentry();
$server = $box->server();

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
//Create the group.
if (isset($_POST['1-name'])) {
	$sentry->createGroup(['name' => $_POST['1-name']]);
	//Create the permission.
} else if (isset($_POST['action'])) {
	if ($_POST['action'] === 'permission') {
		$value = intval($_POST['value']);
		$column = isset($_POST['column']) ? $_POST['column'] : null;
		if (($value & 4) === 4) {
			$server->allow($_POST['group'], $_POST['table'], Server::ACT_CREATE, $column);
		} else {
			$server->deny($_POST['group'], $_POST['table'], Server::ACT_CREATE, $column);
		}
		if (($value & 2) === 2) {
			$server->allow($_POST['group'], $_POST['table'], Server::ACT_UPDATE, $column);
		} else {
			$server->deny($_POST['group'], $_POST['table'], Server::ACT_UPDATE, $column);
		}
		if (($value & 1) === 1) {
			$server->allow($_POST['group'], $_POST['table'], Server::ACT_DELETE, $column);
		} else {
			$server->deny($_POST['group'], $_POST['table'], Server::ACT_DELETE, $column);
		}
		return;
		//Show the column table.
	} else if ($_POST['action'] === 'table') {
		$table = $_POST['table'];
		$tables = $box->db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
		if (!in_array($table, $tables)) {
			return 'Unknown table.';
		}
		$columns = $box->db()->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_COLUMN);
		$groups = $sentry->findAllGroups();
		$data = [];
		$count = 0;
		foreach ($columns as $column) {
			$data[] = [$column];
			foreach ($groups as $group) {
				$perm = $group['permissions'];
				$create = $server->permissionsContain($perm, $table, Server::ACT_CREATE, $column);
				$update = $server->permissionsContain($perm, $table, Server::ACT_UPDATE, $column);
				$delete = $server->permissionsContain($perm, $table, Server::ACT_DELETE, $column);

				$value = 0;
				$value += $create ? 4 : 0;
				$value += $update ? 2 : 0;
				$value += $delete ? 1 : 0;

				$data[$count][] = $value;
			}
			$count++;
		}

		$select = new Select($options, 'column-permission');
		$table = new Table(null, 'table-bordered');
		$groupNames = [];
		foreach ($groups as $group) {
			$groupNames[] = $group['name'];
		}
		$table->setHeaders(array_merge(['Columns'], $groupNames));
		$table->setCellComponent(new Wrapper($select, 'td'));
		$table->getRowComponent()->setUseFirst(true)->setFirstComponent(new Component('td'));
		echo $table->render($data);

		return;
	}
}

$groups = $sentry->findAllGroups();
$tables = $box->db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

$table = new Table(null, 'table-bordered');
$tableWrapper = new Wrapper($table);
$select = new Select($options, 'permission');
$panel = new Panel($tableWrapper, null, 'style="margin: 40px auto 40px auto;"');
$panel->setTitle('Group Permissions');
$groupNames = [];
foreach ($groups as $group) {
	$groupNames[] = $group['name'];
}
$table->setHeaders(array_merge(['Tables'], $groupNames));
$table->setCellComponent(new Wrapper($select, 'td'));
$table->getRowComponent()->setUseFirst(true)->setFirstComponent(new Component('td', 'table-name'));
$tableWrapper->setOnNullInput('There are no groups.');
$data = [];
$count = 0;
if (!empty($groups)) {
	foreach ($tables as $table) {
		$data[] = [$table];
		foreach ($groups as $group) {
			$permissions = $group['permissions'];
			$create = $server->permissionsContain($permissions, $table, Server::ACT_CREATE);
			$update = $server->permissionsContain($permissions, $table, Server::ACT_UPDATE);
			$delete = $server->permissionsContain($permissions, $table, Server::ACT_DELETE);

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

echo $box->header(true, 'WebDreamt', function() {
	?>
	<style>
		th:first-child {
			width: 200px;
		}
		td:first-child {
			width: 200px;
		}
		.table-name {
			cursor: pointer;
		}
		.table-name:hover {
			background-color: lightgoldenrodyellow;
		}
	</style>
	<?php
});
echo $formPanel->render();
echo $panel->render($data);
$tablePanel = new Panel(new Component('div', 'column-table'), 'table-panel', 'style="display:none"');
$tablePanel->setTitle('Column Permissions');
echo $tablePanel->render();

echo $box->footer(true, function() {
	?>
	<script>
		(function () {
			function getFirstRow($element) {
				return $element.parents('tr').children().first().html();
			}

			function getHeader($element) {
				return $element.parents('table').children().first().children().first().children()
						.get($element.parents('td').index()).innerHTML;
			}

			$(document).on('change', '.permission', function (e) {
				var $target = $(e.target);
				var group = getHeader($target);
				var table = getFirstRow($target);
				var value = $target.val();
				$.post(document.URL, {
					table: table,
					group: group,
					value: value,
					action: 'permission'
				}, function (data) {
					if ($.trim(data) !== '') {
						alert(data);
					}
					$.post(document.URL, {
						table: table,
						action: 'table'
					}, function (data) {
						$('.column-table').html(data);
						$('.table-panel').show();
					});
				});
			});
			var selectedTable = null;
			$(document).on('click', '.permission, .table-name', function (e) {
				var $target = $(e.target);
				var table = getFirstRow($target);
				if (selectedTable !== table) {
					selectedTable = table;
					$.post(document.URL, {
						table: table,
						action: 'table'
					}, function (data) {
						$('.column-table').html(data);
						$('.table-panel').show();
					});
				}
			});
			$(document).on('click', '.column-permission', function (e) {
				var $target = $(e.target);
				var table = selectedTable;
				var group = getHeader($target);
				var column = getFirstRow($target);
				var value = $target.val();
				$.post(document.URL, {
					table: table,
					group: group,
					value: value,
					column: column,
					action: 'permission'
				}, function (data) {
					if ($.trim(data) !== '') {
						alert(data);
					}
				});
			});
		})();
	</script>
	<?php
});
