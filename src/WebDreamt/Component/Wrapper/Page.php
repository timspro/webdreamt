<?php

namespace WebDreamt\Component\Wrapper;

use WebDreamt\Box;
use WebDreamt\Component;
use WebDreamt\Component\Wrapper;

class Page extends Wrapper {

	/**
	 * Create a page that uses the header and footer provided by Box. Note that component methods
	 * that rely on modifying the topmost tag won't work.
	 * @param Component $display
	 * @param string $title
	 */
	function __construct(Component $display, $title) {
		parent::__construct($display, null);
		$box = Box::get();
		$this->setAfterOpeningTag($box->header(true, $title))->setBeforeClosingTag($box->footer());
	}

}
