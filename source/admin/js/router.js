a.router = (function(){
	return {
		defloc:'/blog/list/',
		init:function(){
			window.addEventListener('hashchange', function(e){
				a.router.parseHash(window.location.hash.substring(1),e.oldURL.split("#")[1]);
			});
			a.router.parseHash(window.location.hash.substring(1),'/');
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