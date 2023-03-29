jQuery(function($)
{
	$('.kanban-modal-hide').on(
		'click',
		function () {
			$( document ).trigger( '/kanban/modal/hide/' );
		}
	);

	$('.kanban-modal-show').on(
		'click',
		function () {
			$( document ).trigger( '/kanban/modal/show/' );
		}
	);

	$( document ).on(
		'/kanban/modal/show/',
		function () {
			$('#kanban-modal-wrapper').show();
		}
	);

	$( document ).on(
		'/kanban/modal/hide/',
		function () {
			$('#kanban-modal-wrapper').hide();
		}
	);
});