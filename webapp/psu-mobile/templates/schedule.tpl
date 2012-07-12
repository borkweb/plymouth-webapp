{* Begin jQuery Mobile Page *}
{jqm_page id="schedule" class="m-app"}
	{jqm_header title="Schedule" back_button="true"}{/jqm_header}

	{jqm_content}
		<ul id="schedule" data-role="listview" data-theme="d">
		{foreach from="$schedule" key="level_name" item="level"}
			{foreach from="$level" item="term"}
				{foreach from="$term" item="class"}
					<li class="class">
						<h2>{$class->title}</h2>
						<h3>{$class->crs_num}</h3>
						{if $class->meeting_info->building != 'NA'}
							<h4>
								{$class->meeting_info->building}
								{$class->meeting_info->room_number}
							</h4>
							<p>
								{$class->meeting_info->days}
								{$class->meeting_info->begin_time|date_format:"%I:%M"} -
								{$class->meeting_info->end_time|date_format:"%I:%M %P"}
							</p>
						{/if}
						<span class="ui-li-aside">
							{$class->instructors[0]->instructor_name}
						</span>
					</li>
				{/foreach}
			{/foreach}
		{/foreach}
		</ul>
	{/jqm_content}

{/jqm_page}
{* End jQuery Mobile Page *}
