hljs.highlightAll();
hljs.initLineNumbersOnLoad();

$(function () {

	$(".doc-menu .icon").click(function () {
		let $li = $(this).closest("li");
		if ($(this).hasClass("icon-close")) {
			$(this).removeClass("icon-close").addClass("icon-open");

			$li.children("ul").slideDown(function () {
				$li.removeClass("menu-close").addClass("menu-open");
			});
		} else if ($(this).hasClass("icon-open")) {
			$(this).removeClass("icon-open").addClass("icon-close");

			$li.children("ul").slideUp(function () {
				$li.removeClass("menu-open").addClass("menu-close");
			});
		}
	});

	let $docContainer = $("#doc-container");
	let $docMenu = $("#doc-menu");
	let $docMenuToggle = $("#doc-menu-toggle");
	let docMenuPosition1 = $docMenu.offset().top - stickyMenuTopOffset;
	let docMenuHeight = $(window).height() - stickyMenuTopOffset - stickyMenuBottomOffset;
	let docContainerHeight = $docContainer.height();
	let docMenuPosition2 = docMenuPosition1 + docContainerHeight - docMenuHeight;
	let docMenuFixed = false;
	let docMenuUpdate = function() {
		let scrollTop = $(window).scrollTop();
		if (scrollTop >= docMenuPosition1 && scrollTop <= docMenuPosition2) {
			if (!docMenuFixed) {
				$docMenu.css({
					position: "fixed",
					top: stickyMenuTopOffset + "px",
					bottom: stickyMenuBottomOffset + "px",
					transform: ''
				});
				$docMenuToggle.css({
					position: "fixed",
					top: stickyMenuTopOffset + "px",
					bottom: stickyMenuBottomOffset + "px",
					transform: ''
				});
				docMenuFixed = true;
			}
		} else {
			if (docMenuFixed) {
				$docMenu.css({
					position: "relative",
					top: "0",
					bottom: "0",
					height: docMenuHeight + "px",
					transform: ''
				});
				$docMenuToggle.css({
					position: "relative",
					top: "0",
					bottom: "0",
					height: docMenuHeight + "px",
					transform: ''
				});
				docMenuFixed = false;
			}

			if (scrollTop > docMenuPosition2) {
				$docMenu.css({transform: 'translateY(' + (docMenuPosition2 - docMenuPosition1) + 'px)'});
				$docMenuToggle.css({transform: 'translateY(' + (docMenuPosition2 - docMenuPosition1) + 'px)'});
			}
		}
	}

	let docMenuResize = function() {
		docMenuHeight = $(window).height() - stickyMenuTopOffset - stickyMenuBottomOffset;
		docContainerHeight = $docContainer.height();

		docMenuPosition2 = docMenuPosition1 + docContainerHeight - docMenuHeight;
		docMenuUpdate();
	};

	$(window).scroll(docMenuUpdate);
	$(window).resize(docMenuResize);

	let $docMenuToggleOn = $("#doc-menu-toggle-on");
	let $docMenuToggleOff = $("#doc-menu-toggle-off");
	$docMenuToggleOn.click(function () {
		$docContainer.removeClass("doc-menu-off").addClass("doc-menu-on");
		docMenuResize();
	});

	$docMenuToggleOff.click(function () {
		$docContainer.removeClass("doc-menu-on").addClass("doc-menu-off");
		docMenuResize();
	});


	let counter = 0;
	$("pre").each(function () {
		if ($(this).has("code")) {
			$(this).addClass("copy-code");
			$(this).prepend('<button class="be-btn be-btn-sm btn-copy-code">复制</button>');
			counter++;
		}
	});

	if (counter > 0) {
		let buttons = new ClipboardJS('.btn-copy-code', {
			target:function(trigger){
				return trigger.nextElementSibling;
			}
		});

		buttons.on('success',function(e) {
			//e.clearSelection();
			let $trigger = $(e.trigger);
			$trigger.addClass("be-btn-gray").html("代码已复制");
			setTimeout(function () {
				$trigger.removeClass("be-btn-gray").html("复制");
			}, 3000);
		});

		buttons.on('error',function(e) {});
	}

});