{if $model_info.price.min == $model_info.price.max}
	{assign var="price" value=$model_info.price.min|string_format:"%.2f"}
	{assign var="formated_price" value="$`$price`"}
{else}
	{assign var="price_min" value=$model_info.price.min|string_format:"%.2f"}
	{assign var="price_max" value=$model_info.price.max|string_format:"%.2f"}
	{assign var="formated_price" value="$`$price_min` to $`$price_max`"}
{/if}
{box size="`$box_size`" 
	 title="<a href=\"`$PHP.BASE_URL`/item/model/`$model_info.model`\">`$model_info.model`</a>" 
	 secondary_title="<a href=\"`$PHP.BASE_URL`/item/model/`$model_info.model`/list\">`$model_info.quantity` in Stock</a>" 
	 title_size="10" 
	 subheader="<a href=\"`$PHP.BASE_URL`/?search_term=`$model_info.manufacturer`\">`$model_info.manufacturer`</a> > <a href=\"`$PHP.BASE_URL`/?search_term=`$model_info.type`\">`$model_info.type`</a> > <a href=\"`$PHP.BASE_URL`/item/price/`$formated_price`\">`$formated_price`</a>"}
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
	<!--
	<h6 class="item-price">
		{if $model_info.price.min == $model_info.price.max}
			${$model_info.price.min|string_format:"%.2f"}
		{else}
			From ${$model_info.price.min|string_format:"%.2f"} to ${$model_info.price.max|string_format:"%.2f"}
		{/if}
	</h6>
	-->
{/box}
