<!-- BEGIN: main -->
<script language="javascript">
	<!--
	// changes div item visability to thediv_state
	function showhide(item, thediv_state){	
		if (document.all) { //IS IE 4 or 5 (or 6 beta)
			eval( "document.all." + item + ".style.visibility = thediv_state");
		}
		if (document.layers){ //IS NETSCAPE 4 or below
			document.layers[item].visibility = thediv_state;
		}
		if (document.getElementById && !document.all){
			thediv = document.getElementById(item);
			thediv.style.visibility = thediv_state;
		}
	}

	-->
</script>


<fieldset>
	<legend>Call Log Statistics</legend>
	<img src="{graph_img_src}" alt="Statistic Graph" />
	<a href="{graph_img_src}&graph_type=csv">Data in csv format</a>
	<form id="StatForm" name="StatForm" action="statistics.html">
		<select name="statistic">
			<option value="top_call_loggers" {top_call_loggers_selected}>Top Loggers</option>
			<option value="top_callers" {top_callers_selected}>Top Callers</option>
			<optgroup label="Calls">
				<option value="calls_by_date" {calls_by_date_selected}>By Date</option>
				<option value="calls_by_category" {calls_by_category_selected}>By Category</option>
				<option value="calls_by_type" {calls_by_type_selected}>By Type</option>
			</optgroup>
			<optgroup label="Test Scoring">
				<option value="tests_by_instructor" {tests_by_instructor_selected}>By Professor</option>
				<option value="tests_by_date" {tests_by_date_selected}>By Date</option>
			</optgroup>
			<optgroup label="Evaluations">
				<option value="evaluations_by_dept" {evaluations_by_dept_selected}>By Department</option>
			</optgroup>
		</select>
		<input type="radio" value="forever" name="time_delimit" onClick="showhide('date_drop_down', 'hidden');" {forever_checked}> Forever</input>
		<input type="radio" value="today" name="time_delimit" checked onClick="showhide('date_drop_down', 'hidden');" {today_checked}> Today</input>
		<input type="radio" value="this_semester" name="time_delimit" onClick="showhide('date_drop_down', 'hidden');" {this_semester_checked}> This Semester</input>
		<input type="radio" value="yesterday" name="time_delimit" onClick="showhide('date_drop_down', 'hidden');" {yesterday_checked}> Yesterday</input>
		<input type="radio" value="date_range" name="time_delimit" onClick="showhide('date_drop_down', 'visible');" {date_range_checked}> Date Range</input>
		<br />
		<div id="date_drop_down" style="visibility:hidden; margin-top:1ex;">
			{begin_date_select} through {end_date_select}
		</div>
		<div class="well"><a href="javascript:document.StatForm.submit()" class="btn">Generate Statistics</a></div>
	</form>
</fieldset>

<div class="box">
<h3>PSU Analytics Reports</h3>
<ul>
	<li><a href="/webapp/analytics/report/calllog-calls-by-hour?psufilter_1[field]=days_ago&amp;psufilter_1[operator]=&lt;%3D&amp;psufilter_1[value]=7">Calls Logged By Hour (Last 7 Days)</a></li>
	<li><a href="/webapp/analytics/report/calllog-calls-by-employee?call_status=open">Open Calls By Employee</a></li>
	<li><a href="/webapp/analytics/report/calllog-calls-by-employee?call_status=closed">Closed Calls By Employee</a></li>
	<li><a href="/webapp/analytics/report/calllog-calls-by-queue">Calls By Queue</a></li>
	<li><a href="/webapp/analytics/report/calllog-calls-by-queue?call_status=open">Open Calls By Queue</a></li>
	<li><a href="/webapp/analytics/report/calllog-calls-by-queue?call_status=closed">Closed Calls By Queue</a></li>
	<li><a href="/webapp/analytics/report/calllog-calls/?psufilter_1%5Bfield%5D=call_status&psufilter_1%5Boperator%5D=LIKE&psufilter_1%5Bvalue%5D=closed&psufilter_2%5Bfield%5D=call_date&psufilter_2%5Boperator%5D=%3E%3D&psufilter_2%5Bvalue%5D={week_start}&psufilter_3%5Bfield%5D=call_date2&psufilter_3%5Boperator%5D=%3C%3D&psufilter_3%5Bvalue%5D={week_end}">Closed in Past Week ({week_start} - {week_end})</a></li>
</ul>
</div>
<!-- END: main -->
