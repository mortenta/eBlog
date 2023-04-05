a.blog.edit = (function(){
	var my = {
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
								// Init tabs
								a.s.tabbox.init();
								// Init character counters
								my.initCharCounter($('small[data-area="charcount-heading"]'),50,100);
								my.initCharCounter($('small[data-area="charcount-title"]'),50,60);
								my.initCharCounter($('small[data-area="charcount-meta-description"]'),100,160);
								my.initSERP();
								// Init
								my.initEditor(id);
								my.relatedList.init(id);
								// Init tag picker
								my.tagpicker.init(result.data);
								// Prevent submit
								$('form[name="edit_post"], button[data-action="btn-none"]').submit(function(e){
									e.preventDefault();
									return false;
								});
								// Select main image
								$('button[data-action="select-main-image"]').unbind('click').click(function(){
									a.blog.imgpicker.create(id,function(file){
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
								// Save content
								$('button[data-action="save-content"]').unbind('click').click(function(){
									my.saveContent();
								});
								// Save notes
								$('button[data-action="save-notes"]').unbind('click').click(function(){
									my.saveNotes();
								});
								// Save options
								$('button[data-action="save-options"]').unbind('click').click(function(){
									my.saveOptions();
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
		saveContent:function(){
			a.s.loader.fs.create();
			tinyMCE.triggerSave();
			setTimeout(function(){
				$.ajax({
					type:'POST',
					url:'./api/blog/save/content/',
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
		saveNotes:function(){
			a.s.loader.fs.create();
			tinyMCE.triggerSave();
			setTimeout(function(){
				$.ajax({
					type:'POST',
					url:'./api/blog/save/notes/',
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
		saveOptions:function(){
			a.s.loader.fs.create();
			tinyMCE.triggerSave();
			setTimeout(function(){
				$.ajax({
					type:'POST',
					url:'./api/blog/save/options/',
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
		initCharCounter:function(el,min,max){
			if ($('>input',$(el).parent()).length) {
				$('>input',$(el).parent()).keyup(function(){
					my.charCount($(this),min,max);
				});
				my.charCount($('>input',$(el).parent()),min,max);
			}
			else {
				$('>textarea',$(el).parent()).keyup(function(){
					my.charCount($(this),min,max);
				});
				my.charCount($('>textarea',$(el).parent()),min,max);
			}
		},
		charCount:function(el,min,max){
			var l= false;
			var s = false;
			var d = 0;
			var c = 0;
			var str = '';
			var color = '#000';
			// Count
			c = $(el).val().length;
			if (c<min) {
				l = false;
				s = true;
				d = c - min;
				d = String(d);
			}
			else if (c>max) {
				l = true;
				s = false;
				d = c - max;
				d = String('+'+d);
			}
			else {
				l = false;
				s = false;
				d = 0;
				d = String(d);
			}
			if (!l && !s) {
				str = 'Character count: '+c+' (ideal length)';
				color = 'green';
			}
			else if (s) {
				str = 'Character count: '+c+' (too short by '+d+')';
				color = 'orange';
			}
			else {
				str = 'Character count: '+c+' (too long by '+d+')';
				color = 'red';
			}
			$('>small',el.parent()).css('color',color).html(str);
		},
		initSERP:function(){
			$('input[name="meta_title"]').keyup(function(){
				my.updateSERP();
			});
			$('textarea[name="meta_description"]').keyup(function(){
				my.updateSERP();
			});
			my.updateSERP();
		},
		updateSERP:function(){
			var title = $('input[name="meta_title"]').val();
			var description = $('textarea[name="meta_description"]').val();
			$(".gsr>h2").html(add3Dots(title,60));
			$(".gsr>p").html(add3Dots(description,140));
		},
		initEditor:function(postid){
			tinymce.init({
				selector:'textarea.tinymce',
				height: 500,
				plugins: 'preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern help code',
				toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link unlink image | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat | fullscreen code',
				image_advtab: true,
				image_caption: true,
				image_dimensions: false,
				relative_urls: false,
				content_css: [],
				image_title: true,
				file_picker_types: 'image file media',
				file_picker_callback: function(cb, value, meta) {
					if (meta.filetype=='file') {
						// Pages
						a.blog.pagepicker.create(cb);
					}
					else {
						// Images
						a.blog.imgpicker.create(postid,cb,true,false);
					}
				}
			});
		},
		relatedList:{
			init:function(id){
				my.relatedList.search(id);
				my.relatedList.listRelated(id);
				// Actions
				$('button[data-action="search-rel"]').unbind('click').click(function(){
					my.relatedList.search(id);
				});
				$('input[name="rel-q"]').keypress(function(event){
					if(event.keyCode == 13){
						my.relatedList.search(id);
					}
				});
			},
			search:function(id){
				$('#body div[data-area="related-search-result"]').empty().append(a.blog.loaderContentMini);
				$.ajax({
					type:'GET',
					url:'./api/blog/list/',
					data:{
						skipid:id,
						q:$('input[name="rel-q"]').val(),
						limit:25
					},
					dataType:'json',
					cache: false,
					success:function(result) {
						if (result.success) {
							tpl.run({tpl:'./tpl/blog/edit/related-search-result-list.ejs',data:result,cb:function(content){
								$('#body div[data-area="related-search-result"]').empty().append(content);
								setTimeout(function(){
									$('button[data-action="add-related"]').unbind('click').click(function(){
										my.relatedList.addRel(id,$(this).attr('data-id'));
									});
								},100);
							}});
						}
						else {
							a.s.growl.create('Error','Error loading posts from API',{});
						}
					}
				});
			},
			listRelated:function(id){
				$.ajax({
					type:'GET',
					url:'./api/blog/rel/list/',
					data:{
						id:id
					},
					dataType:'json',
					cache: false,
					success:function(result) {
						if (result.success) {
							tpl.run({tpl:'./tpl/blog/edit/related-list.ejs',data:result,cb:function(content){
								$('#body div[data-area="relatedlist"]').empty().append(content);
								setTimeout(function(){
									$('button[data-action="delete-related"]').unbind('click').click(function(){
										my.relatedList.deleteRel(id,$(this).attr('data-id'));
									});
									$('div[data-area="relatedlist"]>ul').sortable({
										handle: ">div>i",
										stop:function(event,ui){
											my.relatedList.changeOrder(id);
										}
									});
								},100);
							}});
						}
						else {
							a.s.growl.create('Error','Error loading related posts from API',{});
						}
					}
				});
			},
			addRel:function(thisid,id){
				$.ajax({
					type:'POST',
					url:'./api/blog/rel/add/',
					data:{
						id:thisid,
						relid:id
					},
					dataType:'json',
					cache: false,
					success:function(result) {
						// Reload
						my.relatedList.listRelated(thisid);
					}
				});
			},
			deleteRel:function(thisid,id){
				$.ajax({
					type:'POST',
					url:'./api/blog/rel/delete/',
					data:{
						id:thisid,
						relid:id
					},
					dataType:'json',
					cache: false,
					success:function(result) {
						// Reload
						my.relatedList.listRelated(thisid);
					}
				});
			},
			changeOrder:function(id){
				var pair = [];
				$.when(
					$('div[data-area="relatedlist"]>ul>li').each(function(i,e){
						pair[i] = $(e).attr('data-id');
					})
				).then(function(){
					$.ajax({
						type:'POST',
						url:'./api/blog/rel/order/',
						data:{
							id:id,
							order:pair.join(',')
						},
						dataType:'json',
						cache: false,
						success:function(result) {
							if (result.success) {
								a.s.growl.create('Success','Related articles was sucessfully reordered',{});
							}
							else {
								a.s.growl.create('Success','Unable to save reorder. Reload page and try again.',{});
							}
						}
					});
				});
			}
		},
		tagpicker:{
			init:function(result){
				var bres = {
					'info':result
				};
				// List available
				my.tagpicker.listAvailable(function(result){
					var c = '';
					result.list.forEach(function(e){
						c += '<option value="'+e.id+'" data-path="'+e.path+'">'+e.title+'</option>';
					});
					$('select[name="taglist"]').empty().append(c);
				},function(){});
				// List selected
				$('ul.tags').empty();
				if (bres.info.taglist) {
					bres.info.taglist.forEach(function(e){
						$('ul.tags').append('<li data-tid="'+e.id+'"><span>'+e.title+'</span><i data-action="remove-selected-tag" data-tid="'+e.id+'">&#x24E7;</i></li>');
					});
				}
				// Binds
				$('button[data-action="add-tag"]').unbind('click').click(function(){
					my.tagpicker.add($('select[name="taglist"]>option:selected').val(),bres.info.id,function(){
						
					});
				});
				$('button[data-action="edit-tag"]').unbind('click').click(function(){
					my.tagpicker.edit($('select[name="taglist"]>option:selected').val(),function(result){});
				});
				$('button[data-action="delete-tag"]').unbind('click').click(function(){
					my.tagpicker.delete($('select[name="taglist"]>option:selected').val(),function(){
						
					});
				});
				$('button[data-action="create-tag"]').unbind('click').click(function(){
					my.tagpicker.create(function(e){
						// Insert into list
						$('select[name="taglist"]').append('<option value="'+e.tid+'" data-path="'+e.path+'">'+e.title+'</option>');
						$('select[name="taglist"]').val(e.tid);
						my.tagpicker.add($('select[name="taglist"]>option:selected').val(),bres.info.id,function(){
							
						});
					});
				});
				my.tagpicker.bindRemove(bres.info.id);
			},
			bindRemove:function(bid){
				$('ul.tags>li>i[data-action="remove-selected-tag"]').unbind('click').click(function(){
					$(this).parent().hide();
					my.tagpicker.remove($(this).attr('data-tid'),bid,function(){});
				});
			},
			listAvailable:function(cb_success,cb_error){
				$.ajax({
					type:'GET',
					url:'./api/blog/tag/list/',
					data:{},
					dataType:'json',
					cache: false,
					success:function(result) {
						if (result.success) {
							cb_success(result);
						}
						else {
							cb_error();
						}
					}
				});
			},
			add:function(id,bid,cb){
				var title = $('select[name="taglist"]>option[value="'+id+'"]').text();
				$.ajax({
					type:'POST',
					url:'./api/blog/tag/add/',
					data:{
						bid:bid,
						tid:id
					},
					dataType:'json',
					cache: false,
					success:function(result) {
						if (result.success) {
							if ($('ul.tags>li>i[data-tid="'+id+'"]').length == 0) {
								$('ul.tags').append('<li data-tid="'+id+'"><span>'+title+'</span><i data-action="remove-selected-tag" data-tid="'+id+'">&#x24E7;</i></li>');
								my.tagpicker.bindRemove(bid);
							}
						}
						else {
							alert('Error');
						}
					}
				});
			},
			edit:function(id,cb){
				var urlify = function(a){return a.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "-").replace(/^-+|-+$/g, '')};
				var title = $('select[name="taglist"]>option[value="'+id+'"]').text();
				var path = $('select[name="taglist"]>option[value="'+id+'"]').attr('data-path');
				var title = prompt("Title",title);
				if (title != null) {
					var path = prompt("Path",urlify(path));
					if (path != null) {
						$.ajax({
							type:'POST',
							url:'./api/blog/tag/edit/',
							data:{
								title:title,
								path:path,
								tid:id
							},
							dataType:'json',
							cache: false,
							success:function(result) {
								if (result.success) {
									// Update tag list
									my.tagpicker.listAvailable(function(result){
										$('select[name="taglist"]').empty();
										result.list.forEach(function(e){
											$('select[name="taglist"]').append('<option value="'+e.id+'" data-path="'+e.path+'">'+e.title+'</option>');
										});
										setTimeout(function(){
											$('select[name="taglist"]').val(id);
										},100);
									},function(){});
									// Update selected tags
									$('ul.tags>li[data-tid="'+id+'"]>span').text(title);
									// Callback
									cb(result);
								}
								else {
									alert('Error');
								}
							}
						});
					}
				}
			},
			delete:function(id,cb){
				if (confirm('Really delete this tag?')) {
					if (confirm('Sure? Really?')) {
						$.ajax({
							type:'POST',
							url:'./api/blog/tag/delete/',
							data:{
								tid:id
							},
							dataType:'json',
							cache: false,
							success:function(result) {
								if (result.success) {
									$('select[name="taglist"]>option[value="'+id+'"]').remove();
									$('ul.tags>li[data-tid="'+id+'"]').remove();
									cb();
								}
								else {
									alert('Error');
								}
							}
						});
					}
				}
			},
			create:function(cb){
				var urlify = function(a){return a.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "-").replace(/^-+|-+$/g, '')};
				var title = prompt("Title","");
				if (title != null) {
					var path = prompt("Path",urlify(title));
					if (path != null) {
						$.ajax({
							type:'POST',
							url:'./api/blog/tag/create/',
							data:{
								title:title,
								path:path
							},
							dataType:'json',
							cache: false,
							success:function(result) {
								if (result.success) {
									cb(result);
								}
								else {
									alert('Error');
								}
							}
						});
					}
				}
			},
			remove:function(id,bid,cb){
				// Detach from article
				$.ajax({
					type:'POST',
					url:'./api/blog/tag/detach/',
					data:{
						bid:bid,
						tid:id
					},
					dataType:'json',
					cache: false,
					success:function(result) {
						if (result.success) {
							$('ul.tags>li[data-tid="'+id+'"]').remove();
							cb();
						}
						else {
							$('ul.tags>li[data-tid="'+id+'"]').show();
							alert('Error');
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
						if (use_date_in_url) {
							window.open('/blog/post/'+result.data.pubdate+'/'+result.data.url_path+'/');
						}
						else {
							window.open('/blog/post/'+result.data.url_path+'/');
						}
					}
				}
			}).done(function(){
				
			});
		}
	}
	return my;
}());