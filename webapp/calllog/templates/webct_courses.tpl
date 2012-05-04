<!-- BEGIN: main -->
<div id="webCTInnerDiv">
	<!-- BEGIN: semester -->
	<div class="content-info-top content-info" style="width:97%;">{term_name}</div>
		<!-- BEGIN: course_info -->
		<div id="webctTD{i}" class="content-info-main content-info" style="width:96.5%; cursor:pointer;" onMouseDown="addWebCTDetails('{course.CRN}','{caller_user_name}', {i});">{course.TITLE}</div>
		<!-- END: course_info -->
	<!-- END: semester -->

	<!-- BEGIN: no_results_message -->
	<div id="webCTRow1" class="content-info-none content-info" align="center">This user has no Courses</div>
	<!-- END: no_results_message -->
</div>
<!-- END: main -->