<div class="text">
	<a href="./">&lt;- Back to the site</a><br />
	<h3 style="display: inline-block; margin-bottom: 10px; padding-top: 10px;">Feedback</h3>
	<?php 
		if (isset($_GET["sent"])) {
			echo "<br />The form has been sent; thanks a lot for your feedback!<br />If you provided a way to contact you back, I will do so very soon!";
		}
	?>
	<form method="POST" onsubmit="submittt();" action="feedback">
		<span>New ideas? Thoughts on current features? Bugs? I'd love to hear from you!</span><br />
		<br />
		<br />
		<b><label for="contact">How would you like me to reply? </b>(optional)</label><br />
		<noscript>
			You can enter an @twitter account, email address, URL, or anything else that I could reasonably understand.<br/>
		</noscript>
		<input name="contact" id="contact" type="text" style="width: 490px;" placeholder="@twitter, email@address.com, or however you like!"/><br />
		<br />
		<label for="feedback"><b>What do you think?</b></label><br />
		<textarea name="feedback" id="feedback" style="width: 500px; height: 150px;"></textarea><br />
		<input type="submit" id="feedback-submit" value="Send feedback" />
	</form>
	<br />
	<br />
	<a href='./'>Back to the site</a>
	<script>
		function submittt() {
			var subm = document.getElementById("feedback-submit");
			subm.value = "Sending...";
			subm.disabled = "disabled";
			setTimeout("subm.disabled='';", 1500);
		}
	</script>
</div>