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