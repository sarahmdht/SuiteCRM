<?php
/*
This logic hook is used to regenerate an existing PDF but it do:
* Find signature image and turn it from PNG to JPG as TCPDF do not read vector images
* Add signature image to the newly generated PDF as TCPDF creates only and do not update
* Replace the first document with the signed document
* Send email to client with the signed document to be downloded to has his copy of the signed document
*/
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/Sugarpdf/Sugarpdf.php');
require_once('include/tcpdf/tcpdf.php');
require_once('include/upload_file.php');

class GenerateSignedAuditContractPDF
{
    public function generateSignedPDF($bean, $event, $arguments)
    {
        global $sugar_config;
        $result = [];
        $contactId = $bean->contacts_Custom_Module_1contacts_ida;
        $signaturePath = $this->saveSignatureAsJpg($bean, $contactId, $_POST['img_data']);
        $originalFileName = $bean->id;
        $originalFilePath = 'upload://' . $originalFileName;

        try {
            // Verify original PDF exists
            if (!file_exists($originalFilePath)) {
                throw new Exception("Original PDF not found: $originalFilePath");
            }

            // Get PDF template to regenerate content
            $pdfTemplateId = 'f0a16d66-27aa-0509-a455-675852662f0b';
            $pdfTemplate = BeanFactory::getBean('AOS_PDF_Templates', $pdfTemplateId);
            if (empty($pdfTemplate->description)) {
                throw new Exception("PDF template not found or empty");
            }

            // Get related beans
            $accountBean = $this->getRelatedAccount($bean);

            // Replace template variables with signature
            $pdfContent = $pdfTemplate->description;
            $replacements = [
                '$accounts_name' => $accountBean->name,
                '$accounts_person_of_contact_c' => $accountBean->person_of_contact_c,
                '{DATE m-d-Y}' => date('m-d-Y'),
                '$accounts_billing_address_state' => $accountBean->billing_address_state,
                '$accounts_contract_amendments_c' => $accountBean->audit_contract_amendments_c,
                'CUSTOMERSIGNATURE' => '<img src="' . $signaturePath . '" width="120" height="100" />',
            ];
            $pdfContent = html_entity_decode($pdfContent);

            foreach ($replacements as $search => $replace) {
                $pdfContent = str_replace($search, $replace, $pdfContent);
            }
            $GLOBALS['log']->fatal("Final HTML content: " . $pdfContent);

            // Generate new PDF with signature
            $pdf = new TCPDF();
            $pdf->AddPage();
            $pdf->writeHTML($pdfContent, true, false, true, false, '');
            // Save signed PDF
            $signedFileName = $bean->id;
            $signedFilePath = 'upload://' . $signedFileName;

            if (file_exists($signedFilePath)) {
                unlink($signedFilePath);
            }

            $pdf->Output($signedFilePath, 'F');

            // Update contract with signed PDF details
            $bean->file_mime_type = 'application/pdf';
            $bean->file_ext = 'pdf';
            $bean->filename = $signedFileName . '.pdf'; // shown in detail view

            if (isset($bean->file_url)) {
                unset($bean->file_url);
            }

            $bean->save();

            $GLOBALS['log']->fatal("Signed PDF generated successfully: " . $signedFilePath);

            $emailObj = BeanFactory::newBean('Emails');
            $defaults = $emailObj->getSystemDefaultEmail();
            $mail = new SugarPHPMailer();
            $mail->setMailerForSystem();
            $mail->IsHTML(true);
            $mail->From = $defaults['email'];
            isValidEmailAddress($mail->From);
            $mail->FromName = $defaults['name'];
            $template_name = "Signed Document";
            $template = new EmailTemplate();
            $beanArray = [$bean->module_dir => $bean->id];
            $template->retrieve_by_string_fields(array('name' => $template_name));
            $subject = $template->parse_template($template->subject, $beanArray);
            $bodyHtml = '<h3>Dear '. $accountBean->person_of_contact_c . '</h3> ,
                </br>
                <p>We are pleased to inform you that you have successfully signed your contract........</p>
                </br>
                <p>You can download your signed copy of the contract from<a href="' . $sugar_config['site_url'] .
                    '/index.php?entryPoint=download&id=' . $signedFileName . '&type=Custom_Module"> here. </a>
                </br>
                <p>rest of email</p>
                </br>
                <p>Best regards,</p>
                <p>Team</p>';
            $mail->Subject = $subject;
            $mail->Body = $bodyHtml;
            $mail->prepForOutbound();
            $success = true;
            $mail->ClearAddresses();
            $mail->AddAddress($accountBean->email1);
            $success = $mail->Send();
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
            $GLOBALS['log']->fatal("Email sent to {$accountBean->email1} - Success: " . ($success ? 'true' : 'false'));

        } catch (Exception $e) {
            $GLOBALS['log']->error("Error generating signed PDF: " . $e->getMessage());
        }
    }

    protected function saveSignatureAsJpg($bean, $contactId, $base64Data)
    {
        try {
            // 1. Decode the base64 data (works with or without data: URL prefix)
            $base64Data = preg_replace('/^data:image\/(png|jpe?g);base64,/', '', $base64Data);
            $imageData = base64_decode($base64Data);

            if ($imageData === false) {
                throw new Exception("Invalid base64 image data");
            }

            // 2. Create image from string
            $image = imagecreatefromstring($imageData);
            if ($image === false) {
                throw new Exception("Failed to create image from data");
            }

            // 3. Prepare white background (JPG doesn't support transparency)
            $jpgImage = imagecreatetruecolor(imagesx($image), imagesy($image));
            $white = imagecolorallocate($jpgImage, 255, 255, 255);
            imagefill($jpgImage, 0, 0, $white);
            imagecopy($jpgImage, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagedestroy($image);

            // 4. Save as JPG
            $filename = $contactId . '_contact_signature.jpg';
            $filePath = 'upload/' . $filename;

            if (!imagejpeg($jpgImage, $filePath, 90)) { // 90 = quality (0-100)
                throw new Exception("Failed to save JPG image");
            }
            imagedestroy($jpgImage);

            // 5. Update bean
            $bean->signature_file = $filename;
            return $filePath;
        } catch (Exception $e) {
            if (isset($jpgImage)) imagedestroy($jpgImage);
            if (isset($image)) imagedestroy($image);
            throw $e;
        }
    }
    protected function getRelatedAccount($bean)
    {
        $linkedAccounts = $bean->get_linked_beans('accounts_Custom_Module_1', 'Account');
        return !empty($linkedAccounts) ? $linkedAccounts[0] : BeanFactory::newBean('Account');
    }
}
