a.blog = (function(){
	var my = {
		listPage:function(){
			a.s.loader.fs.create();
			$.ajax({
				type:'GET',
				url:'./api/blog/list/',
				data:{},
				dataType:'json',
				cache: false,
				success:function(result) {
					if (result.success) {
						tpl.run({tpl:'./tpl/blog/list.ejs',data:result,cb:function(content){
							$('#body').empty().append(content);
							setTimeout(function(){
								$('#body button[data-action="create"]').unbind().click(function(){
									my.createPost();
								});
							},100);
						}});
					}
					else {
						alert('Error loading posts from API');
					}
				}
			}).done(function(){
				a.s.loader.fs.remove();
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
		},
		editPage:function(id){
			a.s.loader.fs.create();
			$.ajax({
				type:'GET',
				url:'./api/blog/load/',
				data:{
					id:id
				},
				dataType:'json',
				cache: false,
				success:function(result) {
					if (result.success && result.data) {
						tpl.run({tpl:'./tpl/blog/edit.ejs',data:result,cb:function(content){
							$('#body').empty().append(content);
							setTimeout(function(){
								// Actions
								my.initEditor();
								// Select main image
								$('button[data-action="select-main-image"]').unbind('click').click(function(){
									my.filePicker.create(function(){

									},true,false);
								});
								// Save
								$('button[data-action="save"]').unbind('click').click(function(){
									a.s.growl.create('Success','The changes has been saved',{});
								});
							},100);
						}});
					}
					else {
						location.hash = '/blog/list/';
					}
				}
			}).done(function(){
				a.s.loader.fs.remove();
			});
		},
		initEditor:function(){
			tinymce.init({
				selector:'textarea.tinymce',
				height: 500,
				plugins: 'preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools contextmenu colorpicker textpattern help code',
				toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link image | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat | fullscreen code',
				image_advtab: true,
				image_dimensions: false,
				relative_urls: false,
				content_css: [
					'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
					'//www.tinymce.com/css/codepen.min.css'
				],
				image_title: true,
				file_picker_types: 'image file media',
				file_picker_callback: function(cb, value, meta) {
					my.filePicker.create(cb,true,false);
				}
			});
		},
		filePicker:{
			create:function(cb,fullpath,filename_only){
				a.s.popup.create({
					title:'Select image',
					width:900,
					height:500
				});
			}
		}
	}
	return my;
}());