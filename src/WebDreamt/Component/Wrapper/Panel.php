<?php

namespace WebDreamt\Component\Wrapper;

use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

class Panel extends Wrapper {

	/**
	 * Construct a Panel.
	 * @param Component $display
	 */
	function __construct(Component $display, $class = null, $html = null) {
		parent::__construct($display, "panel panel-default $class", $html);
	}

	function renderMe($input = null, $included = null) {
		?>
		<div class="panel-heading">
			<span class="panel-title"><?= $this->title ?></span>
		</div>
		<div class="panel-body">
		<?= $this->render($input, $this) ?>
		</div>
		<?php
	}

}
