a.blog.pagepicker = (function(){
	var my = {
		create:function(cb){
			var token = a.s.popup.create({
				title:'Select page',
				width:900,
				height:500,
				content:a.blog.loaderContent,
				ready:function(token){
					tpl.run({tpl:'./tpl/blog/pagepicker/main.ejs',data:{},cb:function(content){
						$('#popupid_'+token+' .ic').empty().append(content);
						setTimeout(function(){
							// Load file list
							my.listPages(token,cb);
							// Actions
							$('#popupid_'+token+' select[data-filter="display"]').unbind('change').change(function(){
								my.listPages(token,cb);
							});
						},100);
					}});
				}
			});
		},
		listPages:function(token,cb){
			var area = $('#popupid_'+token+' div[data-area="pagelist"]');
			$(area).empty().append(a.blog.loaderContent);
			$.ajax({
				type:'GET',
				url:'./api/blog/list/',
				data:{
					display:$('#popupid_'+token+' select[data-filter="display"]').val()
				},
				dataType:'json',
				cache: false,
				success:function(result) {
					if (result.success) {
						tpl.run({tpl:'./tpl/blog/pagepicker/list.ejs',data:result,cb:function(content){
							$(area).empty().append(content);
							setTimeout(function(){
								$('button[data-action="select"]').unbind('click').click(function(){
									console.log(a.sitesettings);
									if (a.sitesettings.use_date_in_url) {
										var fullpath = a.sitesettings.basepath+'/post/'+$(this).attr('data-pubdate')+'/'+$(this).attr('data-path')+'/';
									}
									else {
										var fullpath = a.sitesettings.basepath+'/post/'+$(this).attr('data-path')+'/';
									}
									cb(fullpath,{
										id:$(this).attr('data-id'),
										title:$(this).attr('data-title')
									});
									a.s.popup.remove(token,function(){});
								});
							},100);
						}});
					}
					else {
						a.s.growl.create('Error','Unable to load pages',{});
					}
				}
			});
		}
	}
	return my;
}());