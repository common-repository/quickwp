(function($)
{
	$('.quickwp input#plugin_install').on('change', function(e)
	{
		var fileName = e.target.value.split( '\\' ).pop();
		var labelVal = $('.quickwp input#plugin_install + label').html();
		if(fileName)
		{
			$('.quickwp input#plugin_install + label').html(fileName);
		}
		else $('.quickwp input#plugin_install + label').html(labelVal);
	});
	if(($('#setting-error-tgmpa a[href*="quickwp-install-plugins&plugin_status=install"]').length || $('#setting-error-tgmpa a[href*="install-required-plugins&plugin_status=install"]').length) && $('.quickwp').length)
	{
		$('<div id="quickwp-loading"><div class="content"><svg class="spin-element" viewBox="0 0 50 50"> <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg><p>Redirecting to install page in a few seconds...</p><button id="cancel-timeout" class="quickwp-btn">Cancel</button></div></div>').appendTo('.quickwp');
		var timeout = setTimeout(function()
		{
			var targetLocation = $('#setting-error-tgmpa a[href*="quickwp-install-plugins"]').attr('href');
			if(typeof targetLocation === 'undefined')
			{
				targetLocation = targetLocation ? targetLocation : $('#setting-error-tgmpa a[href*="install-required-plugins"]').attr('href');
			}
			window.location.href = targetLocation;
		},5000);

		$('#cancel-timeout').on('click',function()
		{
			clearTimeout(timeout);
			$('#quickwp-loading').remove();
			$('#wpbody-content').removeClass('quickwp_fade');
		});
	}
	if(($('body.admin_page_quickwp-install-plugins').length || $('body.admin_page_install-required-plugins').length) && !$('.updated').length)
	{
		$('#wpbody-content').addClass('quickwp_fade');
		$('<div id="quickwp-small-loading"><div class="content"><svg class="spin-element" viewBox="0 0 50 50"> <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg><p>Installing plugins automatically, this can take some time. Please wait...</p><button id="cancel-timeout" class="quickwp-btn">Cancel</button></div></div>').appendTo('#wpbody');
		$('#tgmpa-plugins input[name="plugin[]"]').trigger('click');
		if($('#tgmpa-plugins select[name="action"] option[value="tgmpa-bulk-install"]').length)
		{
			$('#tgmpa-plugins select[name="action"] option[value="tgmpa-bulk-install"]').attr('selected','selected');
			var timeout = setTimeout(function()
			{			
				$('#tgmpa-plugins #doaction').trigger('click');
			},5000);
			$('#cancel-timeout').on('click',function()
			{
				clearTimeout(timeout);
				$('#wpbody-content').removeClass('quickwp_fade');
				$('#quickwp-small-loading').remove();
			});
		}
		else
		{
			$('#wpbody-content').removeClass('quickwp_fade');
			$('#quickwp-small-loading').remove();		
		}
	}
	if(($('body.admin_page_quickwp-install-plugins').length || $('body.admin_page_install-required-plugins').length) && $('.updated').length)
	{
		$('#wpbody-content').addClass('quickwp_hide');
		$('#wpbody-content').after('<div id="quickwp-small-loading"><div class="content"><div class="check"></div><div class="sidetext">Thank you for using QuickWP! The plugins were successfully installed, please wait while we finish up...</div></div></div>');
		setTimeout(function()
		{
			var plugin_url = qwp_custom.plugin_url;
			window.location.replace(plugin_url+'/admin.php?page=quickwp&finish&quickwp_nonce='+qwp_custom.quickwp_nonce);
		},3000);
	}
})(jQuery);