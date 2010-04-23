/*
 * Script for Redirect Mapper UI
 * Version:  1.0
 * Author : Dennison+Wolfe Internet Group
 */ 
 
(
	function(rdmap){
		rdmapVerify = 
		{
		current404: 0,
		max404: 0,
		currentSearchLink: '',
		siteUrl: '',
		init : function(){
					if(rdmap("#redirmap-original-post-page").html() == null){
						return;
					}
					var b=rdmapVerify;
					b.max404 = js_404_links_list.length-1;
					b.current404 = 0;
					b.siteUrl = js_siteUrl;
					rdmap("#previous").click(function(){
												var b=rdmapVerify;
												if(b.current404 <= 0){
													b.current404 = 0;
												}
												else{
													b.current404--;
												}
												b.loadCurrentPage();
												return true;
											});
					rdmap("#next").click(function(){
												var b=rdmapVerify;
												if(b.current404 >= b.max404){
													b.current404 = b.max404;
												}
												else{
													b.current404++;
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
					//debugger;
					b.loadCurrentPage();
					return;
					
				},
		loadCurrentPage : function(){
						//debugger
						var b=this;
						var currentPage = js_404_links_list[b.current404];
						rdmap("#current-url").html(currentPage.original_url);
						rdmap("#search").attr("value", currentPage.post_title);
						rdmap("#redirmap-original-post-page").attr("data", currentPage.original_url);
						rdmap("#redirmap-search-results-list").html('');
						rdmap("#redirmap-search-url-page").attr("data", '');
						rdmap("#original_url").attr("value",'');
						rdmap("#search_match").attr("value",'');
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
										rdmapVerify.currentSearchLink = url;
										return false;
								});
						});
					},
		saveMatch : function(){
						var b=rdmapVerify;
						var originalSlug = js_404_links_list[b.current404].original_slug.replace('.shtml','');
						rdmap("#original_url").attr("value", originalSlug);
						var new_slug = b.currentSearchLink.replace(b.siteUrl, '');
						rdmap("#search_match").attr("value", new_slug);
						
						rdmap('#redirmap-title').attr('value', js_404_links_list[b.current404].post_title);
						rdmap('#old').attr('value', originalSlug);
						rdmap('#redirmap-target').attr('value', new_slug);
						
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
											debugger;
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