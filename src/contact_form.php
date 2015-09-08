<?php  
/*
    Plugin Name: Contact Form
    Plugin URI: https://github.com/st421/contact-form
    Description: Put AJAX-enabled contact forms on your WordPress site using easy shortcodes.
    Author: S. Tyler 
    Version: 1.0 
    Author URI: http://susanltyler.com 
*/

// Hooks, shortcodes, and global variables 
add_shortcode('email_form','submit_email_form'); // shortcode for AJAX email form
add_action('wp_ajax_email_contact','send_email'); // email actions for both logged in users and not
add_action('wp_ajax_nopriv_email_contact','send_email');

/*
 * Javascript/AJAX method called by shortcode that places form on page. 
 * Form is returned as a string so that it is displayed before the_content. 
 * $atts should be supplied in the shortcode in the form of 
 * email="example@example.com".
 */
function submit_email_form($atts) {
	extract(shortcode_atts(array('to_email' => ''), $atts));
	$nonce = wp_create_nonce('nonce_1');
	$ajax_url = admin_url('admin-ajax.php');
?>
<script type='text/javascript'>
<!--
jQuery(document).ready(function(){
	jQuery('#submit_email').click(function() {
		jQuery.ajax({
			type: "post",
			url: '<?php echo $ajax_url; ?>',
			data: {
				action: 'email_contact', 
				fname: jQuery('#fname').val(), 
				femail: jQuery('#femail').val(), 
				to_email: '<?php echo $to_email; ?>', 
				fsubject: jQuery('#fsubject').val(), 
				fmessage: jQuery('#fmessage').val(), 
				_ajax_nonce: '<?php echo $nonce; ?>'
				},
			success: function(data) { 
				jQuery("#email_submitted").html(data);
				jQuery("#email_submitted").fadeIn("fast");
				jQuery("#contactform").fadeOut("slow");
			}
		});
		return false;
	})
})
-->
</script>
<?php

$contactform = '
<form method="post" id="contactform"><input type="text" name="fname" value="Name" id="fname"><br />
<input type="text" name="femail" value="Email" id="femail"><br />
<input type="text" name="fsubject" value="Subject" id="fsubject"><br />
<textarea name="fmessage" id="fmessage" maxlength="1000" cols="45" rows="10">Message</textarea><br />
<input type="submit" value="Submit" id="submit_email" class="submit"></form><div id="email_submitted"></div>
';
return $contactform;
}

/*
 * This function actually sends the email after checking that the message/name/etc. 
 * are not empty and that the given email is valid.
 */  
function send_email() {
	check_ajax_referer('nonce_1');
	$name = $_POST['fname'];
	$to_email = $_POST['to_email'];
	$from_email = $_POST['femail'];
	$subject = $_POST['fsubject'];
	$message = $_POST['fmessage'];
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	$headers .= 'From: ' . $from_email;
	if(validate_email($from_email)) {
		mail($to_email,$subject,$message,$headers);
		echo "Your message has been sent.";
	} else {
		echo "Please try again with a valid email.";
	}
	die();
}

function validate_email($email) {
	$result = false;
	if(filter_var($email,FILTER_VALIDATE_EMAIL)) {
		$result = true;
	} 
	return $result;
}
?>
