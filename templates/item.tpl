{box size="16" title="<a href=\"`$PHP.BASE_URL`/admin/equipment/item/`$item.id`\">`$item.psu_name`</a>" 
      
	 title_size="12" 
	 subheader="<a href=\"`$PHP.BASE_URL`/admin/equipment/item/model/`$item.model`\">`$item.model`</a> > <a href=\"`$PHP.BASE_URL`admin/equipment/filter/?search_term=`$item.manufacturer`\">`$item.manufacturer`</a> > <a href=\"`$PHP.BASE_URL`/?search_term=`$item.type`\">`$item.type`</a> > Serial: <a href=\"`$PHP.BASE_URL`/item/`$item.id`\">`$item.serial`</a>"
	 class="item-box"}
	{if $item.filepath}
		<a href="{$PHP.GLPI_IMAGE_BASE}{$item.filepath}">
			<img class="item-thumb" src="{$PHP.GLPI_IMAGE_BASE}{$item.filepath}" alt="{$model_info.model}" />
		</a>
	{else}
		<img class="item-thumb" src="{$PHP.BASE_URL}/images/thumbs/no_image.jpg" alt="No Image Available" />
	{/if}
	<div class="item-description">
		{$item.description|default:$default_description}	
	</div>
{/box}
