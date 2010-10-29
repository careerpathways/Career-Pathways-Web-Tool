
<script type="text/javascript">
	$(function(){
		$("a.course").click(function(){
			var classes = $(this).parents(".post_chart").attr("class").split(" ");
			var school_id;
			for(var i=0; i<classes.length; i++){
				if(classes[i].substring(0, 7) == "school_"){
					school_id = classes[i].substring(7);
				}
			}
				
			var subject = $(this).children(".course_subject").text();
			var number = $(this).children(".course_number").text();
			$.get("/c/course_description.php",{
				school_id: school_id,
				subject: subject,
				number: number	
			},function(data){
				var de = document.documentElement;
				var w = self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
				var h = self.innerHeight || (de&&de.clientHeight) || document.body.clientHeight;
				$("#course_description").html(data.html);
				var gbw = $("#cdgb_container").width();
				var gbh = $("#cdgb_container").height();
				$("#cdgb_container").css({left: ((w - gbw) / 2) + "px", top: ((h / 2) - gbh) + "px"}).show();
			}, "json");
		});
	});

	function close_course_greybox(){
		$("#cdgb_container").hide();
		$("#course_description").html("");
	}
</script>

<div id="cdgb_container" style="position: absolute; width:500px; margin; 30px auto; background: white; border: 3px #ccc solid; display:none;">
	<div style="position: relative;">
		<a href="javascript:close_course_greybox();" class="cdgb_close" style="position: absolute; right: 0; top: 0; font-size: 18pt; text-decoration: none;">x</a>
		<div id="course_description" style="padding: 10px;"></div>
	</div>
</div>
