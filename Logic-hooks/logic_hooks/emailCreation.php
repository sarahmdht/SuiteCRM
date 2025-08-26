<?php
// ---
// title: "Email Logichook"
// date: 2025-08-26T12:00:00+01:00
// author: Sara Medhat
// ---

/*In order to Create and Send an email to either the assigned user, contact, or anyone whenever:
* sending an email when a field changes.
* detecting field change using $bean->fetched_row.
* Using Email Template in hook.
*/

// You can simply add this into your hook When data are static:

$mail = new SugarPHPMailer();
$mail->setMailerForSystem();
$mail->From = "noreply@example.com";
$mail->FromName = "SuiteCRM System";
$mail->Subject = "Test Email";
$mail->Body = "This is a test email.";
$mail->AddAddress("user@example.com");
$mail->Send();

// When CRM values used:

$emailObj = BeanFactory::newBean('Emails');
$defaults = $emailObj->getSystemDefaultEmail();
$mail = new SugarPHPMailer();
$mail->setMailerForSystem();
$mail->From = $defaults['email'];
$mail->FromName = $defaults['name'];
$mail->Subject = "Test Email";
$mail->Body = "This is a test email.";

// Or, if you have already an email template use this:

$emailObj = BeanFactory::newBean('Emails');
$defaults = $emailObj->getSystemDefaultEmail();
$mail = new SugarPHPMailer();
$mail->setMailerForSystem();
$mail->From = $defaults['email'];
$mail->FromName = $defaults['name'];
$template_name = "Template Name";
$template = new EmailTemplate();

if ($template->retrieve_by_string_fields(array('name' => $template_name))) {
$beanArray = [$bean->module_dir => $bean->id];
$subject  = $template->parse_template($template->subject, $beanArray);
$body = $template->parse_template($template->body, $beanArray);
$mail->Subject = $subject;
$mail->Body = $body;
}

// “Make sure the key in $beanArray matches the module your template was created for (e.g., 'Accounts' => $bean->id). Otherwise, merge fields will stay as raw text.”

// Then to add the eamil recipient:

$user = BeanFactory::getBean('Users', $bean->assigned_user_id);
$toAddress = $user->email1;
if (!empty($toAddress) && isValidEmailAddress($toAddress)) {
   $mail->ClearAddresses();
   $mail->AddAddress($toAddress);
   $mail->prepForOutbound();
   @$mail->Send();
}