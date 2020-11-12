// JS for the forms' settings page
window.addEventListener('DOMContentLoaded', function(){

	var submitSettings = function() {
		console.log($('#uploadlogo').prop('files')[0]);

		$.ajax({
			url: OC.generateUrl('/apps/forms/settings'),
			type: 'POST',
			data: $('#forms_settings').serialize(),
			success: function(){
				OC.msg.finishedSuccess('#forms_settings_msg', t('forms', 'Saved'));
			},
			error: function(xhr){
				OC.msg.finishedError('#forms_settings_msg', xhr.responseJSON);
			}
		});
	};

	$('#uploadlogo').on('change', function () {
		var fileReader = new FileReader();
		fileReader.onload = function () { $('#uploadLogoData').val(fileReader.result); };
		fileReader.readAsDataURL($('#uploadlogo').prop('files')[0]);
	});

	$('#uploadBg').on('change', function () {
		var fileReader = new FileReader();
		fileReader.onload = function () { $('#uploadBgData').val(fileReader.result); };
		fileReader.readAsDataURL($('#uploadBg').prop('files')[0]);
	});

	var setupCurtain = function () {
		if ($('#enable-access').prop('checked'))
			$('.forms-access').removeClass('forms-access-curtain');
		else
			$('.forms-access').addClass('forms-access-curtain');
	};

	$('#forms_settings_submit').click(submitSettings);
	$('#enable-access').change(setupCurtain);
	setupCurtain();
});
