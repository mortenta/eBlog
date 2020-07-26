a.blog = (function(){
	var my = {
		loaderContent:'<div class="inline-loader"><span class="spinner">&#9696;</span></div>',
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
								my.initEditor(id);
								// Select main image
								$('button[data-action="select-main-image"]').unbind('click').click(function(){
									my.imgPicker.create(id,function(file){
										$('input[name="img"]').val(file);
									},false,true);
								});
								// Publish change
								$('input[type="checkbox"][name="published"]').unbind('change').change(function(){
									if ($(this).is(':checked')) {
										my.publishPost(id);
									}
									else {
										my.unpublishPost(id);
									}
								});
								// Save
								$('button[data-action="save"]').unbind('click').click(function(){
									my.saveBlog();
								});
								// Delete
								$('button[data-action="delete"]').unbind('click').click(function(){
									my.deletePost(id);
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
		saveBlog:function(){
			a.s.loader.fs.create();
			tinyMCE.triggerSave();
			setTimeout(function(){
				$.ajax({
					type:'POST',
					url:'./api/blog/save/',
					data:$('form[name="edit_post"]').serialize(),
					dataType:'json',
					cache: false,
					success:function(result) {
						if (result.success) {
							a.s.growl.create('Success','The changes was saved.',{});
						}
						else {
							a.s.growl.create('Error','Unable to save changes. Reload and try again.',{});
						}
					}
				}).done(function(){
					a.s.loader.fs.remove();
				});
			},100);
		},
		publishPost:function(id){
			$.ajax({
				type:'POST',
				url:'./api/blog/publish/',
				data:{
					id:id
				},
				dataType:'json',
				cache: false,
				success:function(result) {
					if (result.success) {
						a.s.growl.create('Success','The blog post has been published',{});
					}
					else {
						a.s.growl.create('Error','Unable to publish blog post',{});
					}
				}
			});
		},
		unpublishPost:function(id){
			$.ajax({
				type:'POST',
				url:'./api/blog/unpublish/',
				data:{
					id:id
				},
				dataType:'json',
				cache: false,
				success:function(result) {
					if (result.success) {
						a.s.growl.create('Success','The blog post has been unpublished',{});
					}
					else {
						a.s.growl.create('Error','Unable to unpublish blog post',{});
					}
				}
			});
		},
		deletePost:function(id){
			if (confirm('Do you really want to delete this blog post?')) {
				a.s.loader.fs.create();
				$.ajax({
					type:'POST',
					url:'./api/blog/delete/',
					data:{
						id:id
					},
					dataType:'json',
					cache: false,
					success:function(result) {
						if (result.success) {
							a.s.growl.create('Success','The blog post has been deleted',{});
							location.hash = '/blog/list/';
						}
						else {
							a.s.growl.create('Error',result.error,{});
						}
					}
				}).done(function(){
					a.s.loader.fs.remove();
				});
			}
		},
		initEditor:function(postid){
			tinymce.init({
				selector:'textarea.tinymce',
				height: 500,
				plugins: 'preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern help code',
				toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link image | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat | fullscreen code',
				image_advtab: true,
				image_dimensions: false,
				relative_urls: false,
				content_css: [],
				image_title: true,
				file_picker_types: 'image file media',
				file_picker_callback: function(cb, value, meta) {
					if (meta.filetype=='file') {
						// Pages
						my.pagePicker.create(cb);
					}
					else {
						// Images
						my.imgPicker.create(postid,cb,true,false);
					}
				}
			});
		},
		imgPicker:{
			create:function(postid,cb,full_url,filename_only){
				var token = a.s.popup.create({
					title:'Select image',
					width:900,
					height:500,
					content:my.loaderContent,
					ready:function(token){
						tpl.run({tpl:'./tpl/blog/imgpicker/main.ejs',data:{},cb:function(content){
							$('#popupid_'+token+' .ic').empty().append(content);
							setTimeout(function(){
								// Load file list
								my.imgPicker.listFiles(postid,token,cb,full_url,filename_only);
								// Actions
								$("#imgselector").unbind('change').change(function() {
									my.imgPicker.uploadFiles(postid,this.files,$('#popupid_'+token),function(){
										// Reset file picker
										$("#imgselector").val('');
										// Reload list
										my.imgPicker.listFiles(postid,token,cb,full_url,filename_only);
									});
								});
							},100);
						}});
					}
				});
			},
			listFiles:function(postid,token,cb,full_url,filename_only){
				var area = $('#popupid_'+token+' div[data-area="imglist"]');
				$.ajax({
					type:'GET',
					url:'./api/blog/image/list/',
					data:{
						id:postid
					},
					dataType:'json',
					cache: false,
					success:function(result) {
						if (result.success) {
							tpl.run({tpl:'./tpl/blog/imgpicker/list.ejs',data:result,cb:function(content){
								$(area).empty().append(content);
								setTimeout(function(){
									$('li[data-action="select"]').unbind('click').click(function(){
										if (filename_only) {
											cb($(this).attr('data-filename'),{});
										}
										else {
											if (full_url) {
												// Update with full url
												cb('/blog/img/full/'+$(this).attr('data-filename'),{});
											}
											else {
												cb('/blog/img/full/'+$(this).attr('data-filename'),{});
											}
										}
										a.s.popup.remove(token,function(){});
									});
								},100);
							}});
						}
						else {
							a.s.growl.create('Error','Unable to load images',{});
						}
					}
				});
			},
			uploadFiles:function(postid,files,win,cb){
				if (files.length>0) {
					var totalfiles = files.length;
					var uploadedfiles = 0;
					$(files).each(function(i,file){
						my.imgPicker.uploadFile(postid,file,win,function(){
							uploadedfiles++;
							if (uploadedfiles===totalfiles) {
								cb();
							}
						});
					});
				}
			},
			uploadFile:function(postid,file,win,cb_done){
				var uid = 'fileupload_'+a.s.createToken();
				// Create upload record
				c = '<li class="file" id="'+uid+'">';
				c += '<div class="info">';
				c += '<span class="pct" data-fileupload-area="pct">1%</span><span class="filename">'+file.name.substring(0,50)+'</span>';
				c += '<i class="icon" style="font-style:normal;" data-fileupload-action="close">&times;</i>';
				c += '</div>';
				c += '<div class="prog"><div class="bar" style="width:1%;" data-fileupload-area="progbar"></div></div>';
				c += '</li>';
				$('ul.uploadlist',win).append(c);
				var upload_record =  $('#'+uid);
				var pct_txt = $('#'+uid+' span[data-fileupload-area="pct"]');
				var progbar = $('#'+uid+' div[data-fileupload-area="progbar"]');
				var cancel = $('#'+uid+' i[data-fileupload-action="close"]');
				var data = new FormData();
				data.append('id',postid);
				data.append('file',file);
				var jqxhr = $.ajax({
					type:'POST',
					url:'./api/blog/image/upload/',
					data:data,
					processData:false,
					contentType: false,
					dataType:'json',
					xhr:function(){
						var xhr = new window.XMLHttpRequest();
						xhr.upload.addEventListener("progress", function(evt) {
							if (evt.lengthComputable) {
								var percentComplete = Math.round((evt.loaded / evt.total)*100);
								$(pct_txt).text(percentComplete+'%');
								$(progbar).css('width',percentComplete+'%');
							}
						}, false);
						return xhr;
					},
					success:function(result) {
						if (result.success) {
							$('span[data-fileupload-area="pct"]',upload_record).text('Done!');
							setTimeout(function(){
								upload_record.remove();
								cb_done(false);
							},1000);
						}
						else {
							$('span[data-fileupload-area="pct"]',upload_record).text('Error!');
							setTimeout(function(){
								upload_record.remove();
								cb_done(false);
							},1500);
						}
					}
				}).done(function(){});
				// Cancel button
				$(cancel).unbind('click').click(function(){
					jqxhr.abort();
					upload_record.remove();
					cb_done(false);
				});
			}
		},
		pagePicker:{
			create:function(cb){
				var token = a.s.popup.create({
					title:'Select page',
					width:900,
					height:500,
					content:my.loaderContent,
					ready:function(token){
						tpl.run({tpl:'./tpl/blog/pagepicker/main.ejs',data:{},cb:function(content){
							$('#popupid_'+token+' .ic').empty().append(content);
							setTimeout(function(){
								// Load file list
								my.pagePicker.listPages(token,cb);
								// Actions
								$('#popupid_'+token+' select[data-filter="display"]').unbind('change').change(function(){
									my.pagePicker.listPages(token,cb);
								});
							},100);
						}});
					}
				});
			},
			listPages:function(token,cb){
				var area = $('#popupid_'+token+' div[data-area="pagelist"]');
				$(area).empty().append(my.loaderContent);
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
										cb('/blog/post/'+$(this).attr('data-path')+'/',{
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
		},
		gotoPost:function(id){
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
						window.open('/blog/post/'+result.data.url_path+'/');
					}
				}
			}).done(function(){
				
			});
		}
	}
	return my;
}());