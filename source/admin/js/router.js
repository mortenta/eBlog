a.sitesettings = {};
a.router = (function(){
	return {
		defloc:'/blog/list/',
		init:function(){
			// Load site settings
			$.ajax({
				type:'GET',
				url:'./api/sitesettings/load/',
				data:{},
				dataType:'json',
				cache: false,
				success:function(result) {
					a.sitesettings = result.site_settings;
					if (result.success) {
						// Change page by hash
						window.addEventListener('hashchange', function(e){
							a.router.parseHash(window.location.hash.substring(1),e.oldURL.split("#")[1]);
						});
						a.router.parseHash(window.location.hash.substring(1),'/');
					}
					else {
						alert('Error loading site settings, check config.php');
					}
				}
			}).done(function(){});
		},
		parseHash:function(inp,lasthash){
			if (!inp) {
				window.location.hash = a.router.defloc;
			}
			else {
				var parts = inp.split('/').filter(function(e){return e != ''});
				var lhparts = [];
				if (!(typeof(lasthash)==='undefined')) {
					lhparts = lasthash.split('/').filter(function(e){return e != ''});
				}
				if (parts.length > 0) {
					if (parts[0] == 'blog') {
						if (parts[1] == 'list') {
							a.blog.listPage();
						}
						else if (parts[1] == 'edit') {
							a.blog.edit.editPage(parts[2]);
						}
					}
					else {
						window.location.hash = a.router.defloc;
					}
				}
				else {
					window.location.hash = a.router.defloc;
				}
			}
		}
	}
}());