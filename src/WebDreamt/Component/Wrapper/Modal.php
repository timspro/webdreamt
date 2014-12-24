<?php

namespace WebDreamt\Component\Wrapper;

use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

class Modal extends Wrapper {

	/**
	 * Any buttons to add to the modal.
	 * @var array
	 */
	protected $buttons = [];

	/**
	 * Constructs a modal.
	 * @param Component $display
	 */
	protected function __construct(Component $display) {
		parent::__construct($display, 'modal fade');
	}

	/**
	 * Add buttons that will be rendered with the modal.
	 * @param array $buttons The keys should be strings of class names and the value should be the
	 * text for the button.
	 * @return self
	 */
	function addButtons(array $buttons) {
		$this->buttons = array_merge($this->buttons, $buttons);
		return $this;
	}

	/**
	 * Get the buttons that will be rendered with the modal. Note that this does not include
	 * the close button.
	 * @return array
	 */
	function getButtons() {
		return $this->buttons;
	}

	protected function renderMe($input = null, $included = null) {
		?>
		<div class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
						</button>
						<span class="modal-title"><?= $this->title ?></span>
					</div>
					<div class="modal-body">
						<?php $this->renderMe($input, $this) ?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default wd-btn-close" data-dismiss="modal">Close</button>
						<?php
						foreach ($this->buttons as $class => $text) {
							?>
							<button type="button" class="btn <?= $class ?>"><?= $text ?></button>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

}
