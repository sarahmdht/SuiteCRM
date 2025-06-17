<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/Sugarpdf/Sugarpdf.php');
require_once('include/tcpdf/tcpdf.php');
require_once('include/upload_file.php');

class GenerateSignedCustomContractPDF
{
    public function generateSignedPDF($bean, $event, $arguments)
    {
        $contactId = $bean->contacts_relation_field_ida; //replace with contact field name
        $signaturePath = $this->saveSignatureAsJpg($bean, $contactId, $_POST['img_data']);
        $originalFileName = $bean->id;
        $originalFilePath = 'upload://' . $originalFileName;

        try {
            // Verify original PDF exists
            if (!file_exists($originalFilePath)) {
                throw new Exception("Original PDF not found: $originalFilePath");
            }

            // Get PDF template to regenerate content
            $pdfTemplateId = 'put pdf template ID her';
            $pdfTemplate = BeanFactory::getBean('AOS_PDF_Templates', $pdfTemplateId);
            if (empty($pdfTemplate->description)) {
                throw new Exception("PDF template not found or empty");
            }

            // Get related beans
            $accountBean = $this->getRelatedAccount($bean);
            $contactBean = BeanFactory::getBean('Contacts', $contactId);

            // Replace template variables with signature
            $pdfContent = $pdfTemplate->description;
            $replacements = [
                '$accounts_name' => $accountBean->name,
                '$accounts_person_of_contact_c' => $accountBean->person_of_contact_c,
                '{DATE m-d-Y}' => date('m-d-Y'),
                '$accounts_billing_address_state' => $accountBean->billing_address_state,
                '$accounts_contract_amendments_c' => $accountBean->audit_contract_amendments_c,
                'CUSTOMERSIGNATURE' =>'<img src="' . $signaturePath . '" width="120" height="100" />',
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
            $bean->signed_contract_c = $signedFileName;

            if (isset($bean->file_url)) {
                unset($bean->file_url);
            }

            $bean->save();

            $GLOBALS['log']->fatal("Signed PDF generated successfully: " . $signedFilePath);
        } catch (Exception $e) {
            $GLOBALS['log']->error("Error generating signed PDF: " . $e->getMessage());
        }
    }

protected function saveSignatureAsJpg($bean, $contactId, $base64Data) {
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
        $linkedAccounts = $bean->get_linked_beans('relationship name', 'Account');
        return !empty($linkedAccounts) ? $linkedAccounts[0] : BeanFactory::newBean('Account');
    }
}