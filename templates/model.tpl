{box size="`$box_size`" 
	 title="<a href=\"`$PHP.BASE_URL`/admin/equipment/`$reservation_idx`/item/model/`$model_info.model`/list\">`$model_info.model`</a>" 
	 secondary_title="<a href=\"`$PHP.BASE_URL`/admin/equipment/`$reservation_idx`/item/model/`$model_info.model`/list\">`$model_info.quantity` Available</a>" 
	 title_size="10" 
	 }
	
		<div class="item-description">
			{$model_info.description|default:$default_description}
		</div>
	<div class="clear"></div>
{/box}
