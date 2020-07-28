a.blog = (function(){
	var my = {
		loaderContent:'<div class="inline-loader"><span class="spinner">&#9696;</span></div>',
		loaderContentMini:'<div class="inline-loader mini"><span class="spinner">&#9696;</span></div>',
		listPage:function(){
			tpl.run({tpl:'./tpl/blog/listpage.ejs',data:{},cb:function(content){
				$('#body').empty().append(content);
				// Create action
				$('#body button[data-action="create"]').unbind('click').click(function(){
					my.createPost();
				});
				// Display filter action
				$('#body select[data-filter="display"]').unbind('change').change(function(){
					my.listPosts();
				});
				// List posts
				my.listPosts();
			}});
		},
		listPosts:function(){
			$('#body div[data-area="list"]').empty().append(my.loaderContent);
			$.ajax({
				type:'GET',
				url:'./api/blog/list/',
				data:{
					display:$('#body select[data-filter="display"]').val()
				},
				dataType:'json',
				cache: false,
				success:function(result) {
					if (result.success) {
						tpl.run({tpl:'./tpl/blog/list.ejs',data:result,cb:function(content){
							$('#body div[data-area="list"]').empty().append(content);
						}});
					}
					else {
						a.s.growl.create('Error','Error loading posts from API',{});
					}
				}
			}).done(function(){
				
			});
		},
		createPost:function(){
			var title = prompt("Blog post title:", "");
			if (title != null && title.length>0) {
				a.s.loader.fs.create();
				$.ajax({
					type:'POST',
					url:'./api/blog/create/',
					data:{
						title:title
					},
					dataType:'json',
					cache: false,
					success:function(result) {
						if (result.success && result.id) {
							location.hash = '#/blog/edit/'+result.id+'/';
						}
						else {
							alert('Error creating blog post');
						}
					}
				}).done(function(){
					a.s.loader.fs.remove();
				});
			}
		}
	}
	return my;
}());