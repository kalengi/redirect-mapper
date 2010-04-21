/*
 * Script for Redirect Mapper config screen
 * Version:  1.0
 * Author : Dennison+Wolfe Internet Group
 */ 
 
(
	function(rlmc){
		redirmapConfig = 
		{
		init : function(){
					if(rlmc("#redirmap-list").html() == null){
						return;
					}
					var b=this; 
					b.rows=rlmc("tr.list_item");
					b.addEvents(b.rows);
					
				},
		addEvents : function(b){
						b.each(function(){
							row = rlmc(this);
							var chx = rlmc(row.find("input.category_check:first"));
							if(chx.attr("checked")){
								chx.addClass("selected");
							}
							chx.click(function(){
										redirmapConfig.updateDefault(this);
										return true
									});
							row.find("input.default_category_radio:first").click(function(){
																var radio = rlmc(this);
																var catID = radio.attr("value");
																rlmc("#redirmap_default_category").attr("value", catID);
																return true
															})
							})
					},
		updateDefault : function(chx){
						chx = rlmc(chx);
						var catID = chx.attr("value");
						var radio = rlmc("input.default_category_radio-"+catID);
						if(chx.attr("checked")){
							chx.addClass("selected");
							radio.removeAttr("disabled");
							if(rlmc("#redirmap_default_category").attr("value") == '0'){
								radio.attr("checked", "checked");
								rlmc("#redirmap_default_category").attr("value", catID);
							}
							
							var linkOrder = rlmc("#redirmap_category_link_order_"+catID);
							if(linkOrder.html() == null){
								linkOrder = rlmc("#redirmap_category_link_order").clone(true);
								rlmc(linkOrder).attr("id", "redirmap_category_link_order_"+catID);
								var catIndex = rlmc(chx).attr("name").substr(28);
								rlmc(linkOrder).attr("name", "redirmap_category_link_order"+catIndex);
								var sortableLinks = rlmc("#category_links_list_"+catID);
								rlmc(sortableLinks).sortable();
								var linkOrderData = rlmc(sortableLinks).sortable('serialize');
								rlmc(linkOrder).attr("value", linkOrderData);
								var linkOrderPos = rlmc("#inline_list_"+catID);
								rlmc(linkOrderPos).before(linkOrder);
							}
						}
						else{
							radio.attr("disabled", "disabled");
							radio.removeAttr("checked");
							chx.removeClass("selected");
							
							if(rlmc("#redirmap_default_category").attr("value") == catID){
								this.findNewDefault();
							}

							var linkOrder = rlmc("#redirmap_category_link_order_"+catID);
							if(linkOrder.html() !== null){
								rlmc(linkOrder).remove();
							}
						}
					},
		findNewDefault : function(){
						var chx =  rlmc("input.category_check.selected:first");
						if(chx.html() !== null){
							var catID = chx.attr("value");
							var radio = rlmc("input.default_category_radio-"+catID);
							radio.attr("checked", "checked");
							rlmc("#redirmap_default_category").attr("value", catID);
						}
						else{
							rlmc("#redirmap_default_category").attr("value", '0');
						}
					}
					
		};
		
		rlmc(document).ready(function(){
							redirmapConfig.init()
						})
	}
)(jQuery);