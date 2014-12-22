<?php

namespace WebDreamt\Component\Wrapper;

use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

class Panel extends Wrapper {

	/**
	 * Construct a Panel.
	 * @param Component $display
	 */
	function __construct(Component $display) {
		parent::__construct($display);
		$this->appendCssClass('panel panel-default');
	}

	function renderMe($input = null, $included = null) {
		?>
		<div class="panel-heading">
			<span class="panel-title"><?= $this->title ?></span>
		</div>
		<div class="panel-body">
			<?= $this->render($input, static::class) ?>
		</div>
		<?php
	}

}
