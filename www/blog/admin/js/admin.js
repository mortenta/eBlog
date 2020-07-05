var a = (function(my){return my;}(a || {}));
// @prepros-append "router.js"
// @prepros-append "shared.js"
// @prepros-append "blog.js"
// @prepros-append "init.js"
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
							a.blog.editPage(parts[2]);
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
a.s = (function(){
	var my = {
		loader:{
			fs:{
				create:function(){
					var c = '<div id="fsloader">';
					c += '<span><i class="spinner">&#9696;</i></span>';
					c += '</div>';
					$('body').append(c);
					setTimeout(function(){
						a.s.loader.fs.remove();
					}, 8000);
				},
				remove:function(){
					$('#fsloader').fadeOut(50,function(){
						$(this).remove();
					});
				}
			},
		},
		growl:{
			create:function(title,txt,o){
				var duration = o.duration || 2000;
				if(!document.getElementById('growlc')){
					$('body').append('<div id="growlc"></div>');
				}
				var token = a.s.createToken();
				var c = '<div class="notification" id="growl_notif_'+token+'">';
				c += '<i class="close" data-action="remove_growl_notification" style="font-style:normal;">&times;</i>';
				c += '<h5>'+title+'</h5>';
				if (txt) {
					c += '<p>'+txt+'</p>';
				}
				c += '</div>';
				$('#growlc').append(c);
				var win = $('#growl_notif_'+token);
				win.fadeIn(200,function(){
					$('i[data-action="remove_growl_notification"]',win).unbind('click').click(function(){
						a.s.growl.remove(token);
					});
					setTimeout(function(){
						a.s.growl.remove(token);
					},duration);
				});
			},
			remove:function(token){
				var win = $('#growl_notif_'+token);
				win.fadeOut(200,function(){
					win.remove();
					if ($('#growlc>div.notification').length == 0) {
						$('#growlc').remove();
					}
				});
			}
		},
		popup:{
			winlist:[],
			create:function(s){
				// Defaults
				var w = s.width || 400;
				var h = s.height || 300;
				var title = s.title || '';
				var inpc = s.content || '';
				var show_actions = s.show_actions || false;
				var actions_content = s.actions_content || '';
				var btntitle = s.btntitle || '';
				var add_cancel = s.add_cancel || false;
				var f_ready = s.ready || function(){};
				var f_close = s.close || function(){};
				var f_submit = s.submit || function(){};
				var submit_no_loader = s.submit_no_loader || false;
				var bgcolor = s.bgcolor || '#fff';
				// Get unique token
				var token = a.s.createToken();
				// Create popup content
				var cnt = '';
				cnt += '<div class="popup" id="popupid_'+token+'">';
				cnt += '<div class="popupwin" style="background-color:'+bgcolor+';">';
				cnt += '<div class="popupwinbar">';
				cnt += '<h4>'+title+'</h4>';
				cnt += '</div>';
				cnt += '<i class="close">&times;</i>';
				cnt += '<div class="ic">'+inpc+'</div>';
				if (btntitle || show_actions) {
					cnt += '<div class="actions" style="background-color:#fff;"><div class="row-table-nobr autowidth">';
					if (actions_content) {
						cnt += actions_content;
					}
					else {
						if (add_cancel) {
							cnt += '<div class="col left"><button class="btn btn-link" data-btn="btn_cancel"><span>Cancel</span></button></div>';
						}
						if (btntitle) {
							cnt += '<div class="col right"><button class="btn btn-success" data-btn="btn_submit"><i class="fa fa-spinner fa-spin" style="display:none;"></i><span>'+btntitle+'</span></button></div>';
						}
					}
					cnt += '</div></div>';
				}
				cnt += '</div>';
				cnt += '</div>';
				// Handle container and winlist
				if (a.s.popup.winlist.length == 0) {
					// Create container
					$('body').append('<div id="popup_container"></div>');
					$('#popup_container').fadeIn(50,function(){});
				}
				a.s.popup.winlist.push(token);
				// Make other popups uncurrent
				$('#popup_container .popup').attr('data-active-popup','false');
				// Add popup
				$('#popup_container').append(cnt);
				// Create sizes
				var winw = window.innerWidth;
				var winh = window.innerHeight;
				// Max dimensions
				if (winw < w) {
					w = winw;
				}
				if (winh < h) {
					h = winh;
				}
				if (winw <= 768) {
					// Full size window on mobile devices
					w = winw;
					h = winh;
				}
				var whalf = w/2;
				var hhalf = h/2;
				var ch = h - 95;
				var chnobtn = h - 45;
				var closebtn = w - 41;
				var win_top = winh/2 - hhalf;
				// Define window
				var pwin = $('#popupid_'+token+' .popupwin');
				// Size
				$(pwin).css('width',w+'px');
				$(pwin).css('height',h+'px');
				// Window alignment
				$(pwin).css('left','calc(50% - '+whalf+'px)');
				//$(pwin).css('top','-'+h+'px');
				$(pwin).css('top',win_top+'px');
				// Close button
				$('i.close',pwin).css('left',closebtn+'px');
				// Bottom button
				if (btntitle || show_actions) {
					$('.ic',pwin).css('height',ch+'px');
				}
				else {
					$('.ic',pwin).css('height',chnobtn+'px');
				}
				// Display window
				$('#popupid_'+token+' .popupwin').animate({
					//"top": win_top+"px",
					"opacity": 1
				},200);
				// Append popup to escape button
				a.s.keymng.escList.push({
					'token':token,
					'run':function(){
						a.s.popup.remove(token,f_close);
					}
				});
				// Enable submit button click, if present
				if (btntitle) {
					$('#popupid_'+token+' button[data-btn="btn_submit"]').unbind('click').click(function(){
						if (!submit_no_loader) {
							a.s.loader.fs.create();
						}
						f_submit(function(close_window){
							if (close_window===undefined) {
								close_window=true;
							}
							if (close_window) {
								a.s.popup.remove(token,f_close);
							}
							if (!submit_no_loader) {
								a.s.loader.fs.remove();
							}
						});
					});
				}
				if (add_cancel) {
					$('#popupid_'+token+' button[data-btn="btn_cancel"]').unbind('click').click(function(){
						a.s.popup.remove(token,f_close);
					});
				}
				// Enable remove click
				$('i.close',pwin).click(function(){
					a.s.popup.remove(token,f_close);
				});
				// Run ready function
				f_ready(token);
				// Make sure #popup_container is visible and has elements
				setTimeout(function(){
					if ($("#popup_container").children().length > 0) {
						if ($("#popup_container").is(":visible") == false) {
							$('#popup_container').fadeIn(50,function(){});
						}
					}
					else {
						a.s.popup.winlist = [];
						$("#popup_container").remove();
					}
				},500);
				return token;
			},
			remove:function(token,cb_close){
				$('#popupid_'+token).addClass('remove');
				$('#popupid_'+token).fadeOut(100,function(){
					$('#popupid_'+token).remove();
					a.s.keymng.escRemove(token);
					// Remove from winlist
					a.s.popup.winlist.splice(a.s.popup.winlist.indexOf(token),1);
					// If winlist is empty, remove #popup_container
					if (a.s.popup.winlist.length == 0) {
						$('#popup_container').fadeOut(100,function(){
							$(this).remove();
							cb_close();
						});
					}
					else {
						cb_close();
					}
				});
			},
			notify:function(title,msg,timeout){
				var token = a.s.popup.create({
					width:300,
					height:100,
					title:title,
					content:'<div class="icontainer space">'+msg+'</div>'
				});
				setTimeout(function(){
					a.s.popup.remove(token,function(){});
				},timeout);
			}
		},
		keymng:{
			init:function(){
				$(document).keydown(function(e){
					if(e.which == 27) {
						a.s.keymng.escKey();
					}
				});	
			},
			escList:[],
			escKey:function(){
				if (a.s.keymng.escList.length !== 0) {
					a.s.keymng.escList[a.s.keymng.escList.length-1].run();
				}
			},
			escRemove:function(token){
				if (a.s.keymng.escList.length !== 0) {
					for (i=0;i<a.s.keymng.escList.length;i++) {
						if (a.s.keymng.escList[i].token == token) {
							a.s.keymng.escList.splice(i,1);
							break;
						}
					}
				}
			}
		},
		createToken:function(){
			return Math.random().toString(36).substr(2);
		}
	}
	return my;
}());



// Template engine, requires ejs and jQuery
var tpl = (function(){
	return {
		run:function(s){
			var tpl = s.tpl || '';
			var data = s.data || {};
			var cb = s.cb || function(str){};
			this.get_tpl(tpl,function(tplstr){
				cb(ejs.render(tplstr,data));
			});
		},
		get_tpl:function(path,cb){
			var str = tpl.search_cache(path);
			if (str) {
				cb(str);
			}
			else {
				$.get({
					url:path,
					success:function(data){
						// Add to cache
						tpl.tpl_cache.push({
							path:path,
							data:data
						});
						// Return data
						cb(data);
					}
				});
			}
		},
		search_cache:function(path){
			var data = false;
			for (var i in tpl.tpl_cache) {
				if (tpl.tpl_cache[i]['path'] == path) {
					data = tpl.tpl_cache[i]['data'];
				}
				break;
			}
			if (data) {
				return data;
			}
			else {
				return false;
			}
		},
		tpl_cache:[]
	}
}());
a.blog = (function(){
	var my = {
		loaderContent:'<div class="inline-loader"><span class="spinner">&#9696;</span></div>',
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
								my.initEditor(id);
								// Select main image
								$('button[data-action="select-main-image"]').unbind('click').click(function(){
									my.filePicker.create(id,function(file){
										$('input[name="img"]').val(file);
									},true,false);
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
					my.filePicker.create(postid,cb,true,false);
				}
			});
		},
		filePicker:{
			create:function(postid,cb,fullpath,filename_only){
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
								my.filePicker.listFiles(postid,token,cb);
								// Actions
								$("#imgselector").unbind('change').change(function() {
									my.filePicker.uploadFiles(postid,this.files,$('#popupid_'+token),function(){
										// Reset file picker
										$("#imgselector").val('');
										// Reload list
										my.filePicker.listFiles(postid,token,cb);
									});
								});
							},100);
						}});
					}
				});
			},
			listFiles:function(postid,token,cb){
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
										cb('/blog/img/full/'+$(this).attr('data-filename'),{});
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
					$(files).each(function(i,file){
						my.filePicker.uploadFile(postid,file,win,function(){
							cb();
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
						window.open('/blog/post/'+moment(result.data.time_published).format('Y-MM-DD')+'/'+result.data.url_path+'/');
					}
				}
			}).done(function(){
				
			});
		}
	}
	return my;
}());
a.router.init();
