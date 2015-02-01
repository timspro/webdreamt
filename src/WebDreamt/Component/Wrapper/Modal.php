<?php

namespace WebDreamt\Component\Wrapper;

use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

/**
 * Create a Bootstrap modal.
 */
class Modal extends Wrapper {

	/**
	 * Any buttons to add to the modal.
	 * @var array
	 */
	protected $buttons = [];
	/**
	 * Buttons to be used by the modal.
	 * @var array
	 */
	protected $withButtons = [];

	/**
	 * Construct a modal. Note that the constructor does not allow you to specify the HTML tag
	 * and requires a value for $display (which can be null).
	 * @param Component $display
	 * @param string $class
	 * @param string $html
	 * @param mixed $input
	 */
	function __construct(Component $display, $class = null, $html = null, $input = null) {
		parent::__construct($display, 'div', "modal fade $class", $html, $input);
	}

	/**
	 * Add buttons that will be rendered with the modal.
	 * @param array $buttons The keys should be strings of class names and the value should be the
	 * text for the button.
	 * @return static
	 */
	function addButtons(array $buttons) {
		$this->buttons = array_merge($this->buttons, $buttons);
		return $this;
	}

	/**
	 * Set buttons that will be reset by render().
	 * @param array $buttons
	 * @return static
	 */
	function useButtons(array $buttons) {
		$this->withButtons = array_merge($this->withButtons, $buttons);
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

	/**
	 * Render the modal. Note that the modal HTML includes unnecessary white space.
	 * @param array $input
	 * @param Component $included
	 * @return string
	 */
	protected function renderInput($input = null, Component $included = null) {
		ob_start();
		?>
		<div class="modal-dialog">
			<div class="modal-content">
				<?php
				if ($this->title !== null) {
					?>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
						</button>
						<span class="modal-title"><?= $this->title ?></span>
					</div>
					<?php
				}
				?>
				<div class="modal-body"><?= $this->display->render($input, $this) ?></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default wd-btn-close" data-dismiss="modal">Close</button>
					<?php
					foreach (array_merge($this->buttons, $this->withButtons) as $class => $text) {
						?>
						<button type="button" class="btn <?= $class ?>"><?= $text ?></button>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		<?php
		$this->withButtons = [];
		return ob_get_clean();
	}

}
