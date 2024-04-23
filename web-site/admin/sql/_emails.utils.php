<?php

function sendEmail(string $destinationEmail, string $subject, string $emailContent, string $lang = 'EN') : void {
    try {
        $lang = 'EN'; // For now

        // echo ("<h2>Password process initiated</h2>" . PHP_EOL);

        $from_email		 = 'pcc@passe-coque.com'; // 'sender@abc.com';    // from mail, sender email address
        
        // Load POST data from HTML form
        $pc_email       = $destinationEmail; // sender email, it will be used in "reply-to" header
        $subject	    = "Boat Club Reservation";
        $message	    = $emailContent;
        // $message       .= ("<a href='http://www.passe-coque.com/php/password.02.php?subscriber-id=$pc_email&lang=$lang'>" . (($lang == "FR") ? "Reset mot de passe" : "Reset password") . "</a><br/>"); // Use the real ID

        $boundary = md5("random"); // define boundary with a md5 hashed value

        // header
        $headers = "MIME-Version: 1.0\r\n";              // Defining the MIME version
        $headers .= "From:".$from_email."\r\n";          // Sender Email (contact)
        $headers .= "Reply-To: ".$pc_email."\r\n";       // Email address to reach back
        $headers .= "Content-Type: multipart/mixed;";    // Defining Content-Type
        $headers .= "boundary = $boundary\r\n";          // Defining the Boundary
                
        try {
            $footer = "<br/><hr/><p>"; 
            $footer .= "<img src='http://www.passe-coque.com/logos/LOGO_PC_rvb.png' width='40'><br/>";  // The full URL of the image.
            $footer .= "The <a href='http://www.passe-coque.com' target='PC'>Passe-Coque</a> web site<br/>"; // Web site
            $footer .= "</p>";
            $fmt_message = str_replace("\n", "\n<br/>", $message);
            $fmt_message .= $footer;
        
            // plain text, or html
            $body = "--$boundary\r\n";
            // $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n"; // To allow HTML artifacts, like links and Co.
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($fmt_message));
                
            // TODO? Bcc in the headers (see https://stackoverflow.com/questions/9525415/php-email-sending-bcc)
            $sentMailResult = mail($pc_email, $subject, $body, $headers);

            if ($sentMailResult) {
                if ($lang == "FR") {
                    echo "Un email pour $pc_email est parti.<br/>" . PHP_EOL;
                } else {
                    echo "Email to $pc_email was sent successfully.<br/>" . PHP_EOL;
                }
                // unlink($name); // delete the file after attachment sent.
            } else {
                if ($lang == "FR") {
                    echo "Oops ! Probl&egrave;me pour $pc_email ...<br/>";
                } else {
                    echo "There was a problem for $pc_email ...<br/>";
                }
                die("Sorry but the email to $pc_email could not be sent. Please go back and try again!");
            }	  
        } catch (Throwable $e) {
            echo "Captured Throwable for connection : " . $e->getMessage() . "<br/>" . PHP_EOL;
        }
        // echo "<hr/>" . PHP_EOL;
    } catch (Throwable $e) {
        echo "[Captured Throwable for " . __FILE__ . " : " . $e . "] <br/>" . PHP_EOL;
    }
}

?>