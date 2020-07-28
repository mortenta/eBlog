a.blog.imgpicker = (function(){
	var my = {
		create:function(postid,cb,full_url,filename_only){
			var token = a.s.popup.create({
				title:'Select image',
				width:900,
				height:500,
				content:a.blog.loaderContent,
				ready:function(token){
					tpl.run({tpl:'./tpl/blog/imgpicker/main.ejs',data:{},cb:function(content){
						$('#popupid_'+token+' .ic').empty().append(content);
						setTimeout(function(){
							// Load file list
							my.listFiles(postid,token,cb,full_url,filename_only);
							// Actions
							$("#imgselector").unbind('change').change(function() {
								my.uploadFiles(postid,this.files,$('#popupid_'+token),function(){
									// Reset file picker
									$("#imgselector").val('');
									// Reload list
									my.listFiles(postid,token,cb,full_url,filename_only);
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
					my.uploadFile(postid,file,win,function(){
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
	}
	return my;
}());