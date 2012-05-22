/*
 * Script for Redirect Mapper UI
 * Version:  1.1
 * Author : Dennison+Wolfe Internet Group
 */ 
 
(
	function(rdmap){
		rdmapVerify = 
		{
		currentRedirect: 0,
		maxRedirect: 0,
		currentSearchLink: '',
		siteUrl: '',
		init : function(){
					if(rdmap("#redirmap-original-post-page").html() == null){
						return;
					}
					var b=rdmapVerify;
					b.maxRedirect = js_redirect_links_list.length-1;
					b.currentRedirect = 0;
					b.siteUrl = js_siteUrl;
					rdmap("#previous").click(function(){
												var b=rdmapVerify;
												if(b.currentRedirect <= 0){
													b.currentRedirect = 0;
												}
												else{
													b.currentRedirect--;
												}
												b.loadCurrentPage();
												return true;
											});
					rdmap("#next").click(function(){
												var b=rdmapVerify;
												if(b.currentRedirect >= b.maxRedirect){
													b.currentRedirect = b.maxRedirect;
												}
												else{
													b.currentRedirect++;
												}
												b.loadCurrentPage();
												return true;
											});
					rdmap("#search_button").click(function(){
												rdmapVerify.searchPosts();
												return true;
											});
					rdmap("#save_match").click(function(){
												rdmapVerify.saveMatch();
												return true;
											});
					rdmap("#match_preview").click(function(){
												rdmapVerify.loadManualLinkPages();
												return true;
											});
					
					b.loadCurrentPage();
					return;
					
				},
		loadCurrentPage : function(){
						
						var b=this;
						var currentPage = js_redirect_links_list[b.currentRedirect];
						rdmap("#current-url").html(currentPage.post_title);
						rdmap("#redirmap-original-post-page").attr("data", currentPage.original_url);
						rdmap("#redirmap-search-results-list").html('');
						rdmap("#redirmap-search-url-page").attr("data", currentPage.new_url);
						rdmap("#original_url").attr("value",currentPage.original_url);
						rdmap("#target_url").attr("value",currentPage.new_url);
					},
		loadManualLinkPages : function(){
						var originalPage = '';  
						var targetPage = '';  
						
						if(rdmap("#original_url").html() !== null){
							originalPage = rdmap("#original_url").attr("value");
						}
						rdmap("#redirmap-original-post-page").attr("data", originalPage);
						
						if(rdmap("#target_url").html() !== null){
							targetPage = rdmap("#target_url").attr("value");
						}
						rdmap("#redirmap-search-url-page").attr("data", targetPage);
						
					},
		searchPosts : function(){
						var b=rdmapVerify;
						rdmap("#redirmap-search-results-list").html('');
						rdmap.ajax({
								url:"/wp-admin/admin-ajax.php",
								type:'GET',
								data:'action=redirmap_action&s=' + rdmap("#search").attr("value"),
								dataType: 'html',
								success:function(result, status, XMLHttpRequest){
											var r=result.split('||');
											var s=rdmap("#redirmap-search-results-list");
											s.html(r[0]);
											rdmap("#ajax_busy").hide();
											rdmapVerify.attachSearchLinkEvent(s);
											return false;
										}
						});
						rdmap("#ajax_busy").show();
					},
		attachSearchLinkEvent : function(s){
						var links=s.find("a.redirmap-search-link");
						links.each(function(){
								rdmap(this).click(function(){
										var url = rdmap(this).attr("href");
										rdmap("#redirmap-search-url-page").attr("data", url);
										rdmap("#target_url").attr("value", url);
										rdmapVerify.currentSearchLink = url;
										return false;
								});
						});
					},
		saveMatch : function(){
						var b=rdmapVerify;
						rdmap('#redirmap-title').attr('value', js_redirect_links_list[b.currentRedirect].post_title);
						rdmap('#old').attr('value', rdmap("#original_url").attr("value"));
						rdmap('#redirmap-target').attr('value', rdmap("#target_url").attr("value"));
						
						rdmap('#redirmap-map-url-redirect').modal({
								overlayId: 'redirmap-overlay',
								containerId: 'redirmap-container', 
								opacity: 60
						});
						var f=rdmap("#redirmap-container form");
						f.submit(function(){
												rdmapVerify.submitRedirect(this);
												return false;
											});
					},
		submitRedirect : function(f){
						f=rdmap(f);
						var data=f.serialize();
						rdmap.ajax({
								url: "/wp-admin/admin-ajax.php",
								type: 'POST',
								data: data,
								dataType: 'html',
								success:function(result, status, XMLHttpRequest){
											var s=rdmap("#info_message");
											var r=result.replace(/&#8203;/gi,'');
											s.html(r);
											var testUrl = s.find("a:last");
											testUrl.attr("target", "_blank");
											s.html('');
											s.append(testUrl);
											s.show();
											rdmap("#redirmap_busy").hide();
											return false;
										}
						});
						rdmap("#redirmap-container input[name=save]").hide();
						rdmap("#redirmap-container input[name=cancel]").hide();
						rdmap("#redirmap_busy").show();
					}
					
		};
		
		rdmap(document).ready(function(){
							rdmapVerify.init()
						})
	}
)(jQuery);