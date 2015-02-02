(function () {
	$('.wd-select2').select2({
		minimumResultsForSearch: 8
	});

	var newId = null;
	function fixForm($form, map) {
		//get ID
		var formId = $form.children('.wd-form-id')[0];
		var oldId = formId.name;
		if (newId === null) {
			newId = 10000;
		}
		while ($('.wd-form > [name="' + newId + '"]').length !== 0) {
			newId++;
		}
		formId.name = newId;
		map[oldId] = newId;

		//form relations
		var $relations = $form.children(".wd-form-relation");
		$.each($relations, function (index, el) {
			var parts = el.name.split(":");
			var newName = '';
			if (parts[0] in map) {
				newName += map[parts[0]];
			} else {
				newName += parts[0];
			}
			newName += ":with:";
			if (parts[2] in map) {
				newName += map[parts[2]];
			} else {
				newName += parts[2];
			}
			el.name = newName;
		});
		//inputs
		var $children = $form.children().children().filter("[name^='" + oldId + ":']");
		$.each($children, function (index, el) {
			var parts = el.name.split(":");
			el.name = map[oldId] + ":" + parts[1];
		});
		//for
		var $children = $form.children().children().filter("[for^='" + oldId + ":']");
		$.each($children, function (index, el) {
			var parts = el.getAttribute('for').split(":");
			el.setAttribute('for', map[oldId] + ":" + parts[1]);
		});
		//subforms
		var $subforms = $form.children(".wd-subform");
		$.each($subforms, function (index, el) {
			newId++;
			fixForm($(el), map);
		});
	}

	var oldForms = {};
	$(document).on('click', '.wd-remove-icon', function (e) {
		var $target = $(this);
		if (!$target.parent().is('a')) {
			var $removable = $target.parent().parent();
			var formId = $removable.attr('wd-another');
			if (typeof formId !== 'undefined') {
				oldForms[formId] = $removable;
			}
			$removable.remove();
		}
	});

	$(document).on('click', '.wd-multiple', function (e) {
		var $target = $(this);
		var formId = $target.attr('wd-another');
		var $form = $('.wd-form[wd-another="' + formId + '"]').last();
		if ($form.length === 0) {
			var $newForm = oldForms[formId];
			delete oldForms[formId];
		} else {
			var $newForm = $form.clone(true);
		}
		$newForm.insertBefore($target);
		fixForm($newForm, {});
		$newForm.find("input[type!='hidden'], textarea, select").val("").change();
	});

	$(document).on('click', '.wd-form-submit', function (e) {
		var $form = $(this).parents('form').first();
		if (typeof $form.attr('method') === 'undefined') {
			$form.attr('method', 'POST');
		}
		$form[0].submit();
	});

	function enable($switch, $other) {
		if ($switch.val() === '') {
			$other.removeAttr('disabled');
		} else {
			$other.attr('disabled', '');
		}
	}

	$(document).on('change', '.wd-is-select', function (e) {
		var $target = $(this);
		var $other = $target.parent().parent().find('.wd-is-input');
		enable($target, $other);
	});

	$(document).on('change', '.wd-is-input', function (e) {
		var $target = $(this);
		var $other = $target.parent().parent().find('.wd-is-select');
		enable($target, $other);
	});

	$(document).on('click', '.wd-modal-submit', function (e) {
		var form = $(this).parent().siblings('.modal-body').find('form')[0];
		if (!form.hasAttribute('method')) {
			form.setAttribute('method', 'POST');
		}
		form.submit();
	});

	function getCookie(name) {
		var value = "; " + document.cookie;
		var parts = value.split("; " + name + "=");
		if (parts.length === 2) {
			return parts.pop().split(";").shift();
		}
	}

	$(document).on('click', '.wd-url', function (e) {
		e.preventDefault();
		//Go ahead and send the CSRF token, althought it isn't necessary if we are just getting modals.
		var returns = typeof $(this).attr('data-wd-return') !== 'undefined';
		$.post($(this).attr('href'), {':csrf': getCookie('wd-csrf-token')}, function (data) {
			if (returns && $.trim(data) !== '') {
				$('body').append(data);
				$('.wd-modal-show').modal('show');
			} else {
				window.location.reload();
			}
		});
	});

	$('.wd-is-input, .wd-is-select').trigger('change');

	$(window).load(function (e) {
		$('.wd-modal-show').modal('show');
	});
})();