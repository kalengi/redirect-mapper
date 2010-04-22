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
		init : function(){
					if(rdmap("#redirmap-original-post-page").html() == null){
						return;
					}
					var b=rdmapVerify;
					b.max404 = js_404_links_list.length-1;
					b.current404 = 0
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
											})
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
											})
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
						//var theHTML = rdmap("#redirmap-original-post").html();
						//rdmap("#redirmap-original-post-page").remove();
						//rdmap("#redirmap-original-post").html(theHTML);
					},
		updateDefault : function(chx){
						chx = rdmap(chx);
						var catID = chx.attr("value");
						var radio = rdmap("input.default_category_radio-"+catID);
						if(chx.attr("checked")){
							chx.addClass("selected");
							radio.removeAttr("disabled");
							if(rdmap("#rdmap_default_category").attr("value") == '0'){
								radio.attr("checked", "checked");
								rdmap("#rdmap_default_category").attr("value", catID);
							}
							
							var linkOrder = rdmap("#rdmap_category_link_order_"+catID);
							if(linkOrder.html() == null){
								linkOrder = rdmap("#rdmap_category_link_order").clone(true);
								rdmap(linkOrder).attr("id", "rdmap_category_link_order_"+catID);
								var catIndex = rdmap(chx).attr("name").substr(28);
								rdmap(linkOrder).attr("name", "rdmap_category_link_order"+catIndex);
								var sortableLinks = rdmap("#category_links_list_"+catID);
								rdmap(sortableLinks).sortable();
								var linkOrderData = rdmap(sortableLinks).sortable('serialize');
								rdmap(linkOrder).attr("value", linkOrderData);
								var linkOrderPos = rdmap("#inline_list_"+catID);
								rdmap(linkOrderPos).before(linkOrder);
							}
						}
						else{
							radio.attr("disabled", "disabled");
							radio.removeAttr("checked");
							chx.removeClass("selected");
							
							if(rdmap("#rdmap_default_category").attr("value") == catID){
								this.findNewDefault();
							}

							var linkOrder = rdmap("#rdmap_category_link_order_"+catID);
							if(linkOrder.html() !== null){
								rdmap(linkOrder).remove();
							}
						}
					},
		findNewDefault : function(){
						var chx =  rdmap("input.category_check.selected:first");
						if(chx.html() !== null){
							var catID = chx.attr("value");
							var radio = rdmap("input.default_category_radio-"+catID);
							radio.attr("checked", "checked");
							rdmap("#rdmap_default_category").attr("value", catID);
						}
						else{
							rdmap("#rdmap_default_category").attr("value", '0');
						}
					}
					
		};
		
		rdmap(document).ready(function(){
							rdmapVerify.init()
						})
	}
)(jQuery);