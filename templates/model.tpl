{box size="`$box_size`" 
	 title="<a href=\"`$PHP.BASE_URL`/admin/equipment/item/model/`$model_info.model`\">`$model_info.model`</a>" 
	 secondary_title="<a href=\"`$PHP.BASE_URL`/admin/equipment/item/model/`$model_info.model`/list\">`$model_info.quantity` in Stock</a>" 
	 title_size="10" 
	 subheader="<a href=\"`$PHP.BASE_URL`/admin/equipment/filter?search_term=`$model_info.manufacturer`\">`$model_info.manufacturer`</a> > <a href=\"`$PHP.BASE_URL`/admin/equipment/filter?search_term=`$model_info.type`\">`$model_info.type`</a> </a>"}
	{if $model_info.filepath}
		<a href="{$PHP.GLPI_IMAGE_BASE}{$model_info.filepath}">
			<img class="item-thumb" src="{$PHP.GLPI_IMAGE_BASE}{$model_info.filepath}" alt="{$model_info.model}" />
		</a>
	{else}
		<img class="item-thumb" src="{$PHP.BASE_URL}/images/thumbs/no_image.jpg" alt="No Image Available" />
	{/if}
	<div class="item-description">
		{$model_info.description|default:$default_description}
	</div>
	<div class="clear"></div>
{/box}
