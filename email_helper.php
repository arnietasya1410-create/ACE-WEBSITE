<?php
// filepath: c:\xampp1\htdocs\ACE\email_helper.php

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If you downloaded manually:
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer-7.0.1/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer-7.0.1/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer-7.0.1/src/SMTP.php';

// If you used Composer, use this instead:
// require __DIR__ . '/vendor/autoload.php';

// Basic mail configuration (edit here or set environment variables)
$ACE_MAIL = [
    'host'       => getenv('ACE_MAIL_HOST') ?: 'smtp.office365.com',
    'port'       => (int)(getenv('ACE_MAIL_PORT') ?: 587),
    'username'   => getenv('ACE_MAIL_USER') ?: 'ace.rcmp@unikl.edu.my',
    'password'   => getenv('ACE_MAIL_PASS') ?: '',
    'from_email' => getenv('ACE_MAIL_FROM') ?: 'ace.rcmp@unikl.edu.my',
    'from_name'  => getenv('ACE_MAIL_FROM_NAME') ?: 'ACE Team',
    'to_email'   => getenv('ACE_MAIL_TO') ?: 'ace.rcmp@unikl.edu.my',
];

/**
 * Send email notification for new query using PHPMailer
 */
function send_query_notification($query_data) {
    $mail = new PHPMailer(true);
    
    try {
        global $ACE_MAIL;
        if (empty($ACE_MAIL['username']) || empty($ACE_MAIL['password'])) {
            return false;
        }
        // ==========================================
        // OPTION 1: Use Microsoft/Outlook SMTP (UniKL Email)
        // ==========================================
        $mail->isSMTP();
        $mail->Host       = $ACE_MAIL['host'];                    
        $mail->SMTPAuth   = true;
        $mail->Username   = $ACE_MAIL['username'];
        $mail->Password   = $ACE_MAIL['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $ACE_MAIL['port'];
        
        // Email Details
        $mail->setFrom($ACE_MAIL['from_email'], 'ACE Programme System');
        $mail->addAddress($ACE_MAIL['to_email']);          // You'll receive emails here
        $mail->addReplyTo($query_data['email'], $query_data['full_name']);
        
        // Content
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = 'New Query Received - ACE Programme';
        $mail->Body    = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #6f42c1, #5a35a8); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #6f42c1; }
                .value { margin-top: 5px; padding: 10px; background: white; border-left: 3px solid #6f42c1; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🔔 New Query Received</h2>
                    <p>ACE Programme Inquiry System</p>
                </div>
                
                <div class='content'>
                    <div class='field'>
                        <div class='label'>Programme:</div>
                        <div class='value'>{$query_data['programme_title']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>Full Name:</div>
                        <div class='value'>{$query_data['full_name']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>Email:</div>
                        <div class='value'>{$query_data['email']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>Phone:</div>
                        <div class='value'>{$query_data['phone']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>Message:</div>
                        <div class='value'>{$query_data['message']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>Submitted At:</div>
                        <div class='value'>{$query_data['submitted_at']}</div>
                    </div>
                    
                    <p style='margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 3px solid #ffc107;'>
                        <strong>⚠️ Action Required:</strong> Please respond to this query as soon as possible.
                    </p>
                </div>
                
                <div class='footer'>
                    <p>This is an automated notification from ACE Programme System</p>
                    <p>© " . date('Y') . " UniKL RCMP - All rights reserved</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send email notification for service inquiry using PHPMailer
 */
function send_inquiry_notification($inquiry_data) {
    $mail = new PHPMailer(true);
    
    try {
        global $ACE_MAIL;
        if (empty($ACE_MAIL['username']) || empty($ACE_MAIL['password'])) {
            return false;
        }
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host       = $ACE_MAIL['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $ACE_MAIL['username'];
        $mail->Password   = $ACE_MAIL['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $ACE_MAIL['port'];
        
        $inquiry_types = [
            'consultancy' => 'Consultancy Services',
            'specialized_course' => 'Specialized Course',
            'coe' => 'Center of Excellence Request',
            'event_space_rental' => 'Event Space Rental Request',
            'short_course' => 'Short Course Request',
            'micro_credential' => 'Micro-Credential Request',
            'certificate' => 'Professional Certificate Request',
            'apel' => 'APEL Request',
            'odl' => 'ODL Request',
            'general' => 'General Inquiry'
        ];
        
        $type_label = $inquiry_types[$inquiry_data['inquiry_type']] ?? 'General Inquiry';
        
        // Email Details
        $mail->setFrom($ACE_MAIL['from_email'], 'ACE Contact System');
        $mail->addAddress($ACE_MAIL['to_email']);
        $mail->addReplyTo($inquiry_data['email'], $inquiry_data['name']);
        
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = "New {$type_label} - ACE Contact Form";
        $mail->Body    = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #6f42c1, #5a35a8); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #6f42c1; }
                .value { margin-top: 5px; padding: 10px; background: white; border-left: 3px solid #6f42c1; }
                .badge { display: inline-block; padding: 5px 15px; background: #6f42c1; color: white; border-radius: 20px; font-size: 12px; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📧 New Service Inquiry</h2>
                    <p><span class='badge'>{$type_label}</span></p>
                </div>
                
                <div class='content'>
                    <div class='field'>
                        <div class='label'>Name:</div>
                        <div class='value'>{$inquiry_data['name']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>Email:</div>
                        <div class='value'>{$inquiry_data['email']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>Phone:</div>
                        <div class='value'>{$inquiry_data['phone']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>Subject:</div>
                        <div class='value'>{$inquiry_data['subject']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>Message:</div>
                        <div class='value'>{$inquiry_data['message']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>Submitted At:</div>
                        <div class='value'>{$inquiry_data['created_at']}</div>
                    </div>
                    
                    <p style='margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 3px solid #ffc107;'>
                        <strong>⚠️ Action Required:</strong> Please respond to this inquiry as soon as possible.
                    </p>
                </div>
                
                <div class='footer'>
                    <p>This is an automated notification from ACE Contact System</p>
                    <p>© " . date('Y') . " UniKL RCMP - All rights reserved</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send OTP code for service inquiry verification
 */
function send_inquiry_otp($toEmail, $toName, $otp) {
    $mail = new PHPMailer(true);
    try {
        global $ACE_MAIL;
        if (empty($ACE_MAIL['username']) || empty($ACE_MAIL['password'])) {
            return ['ok' => false, 'error' => 'SMTP credentials missing'];
        }
        $mail->isSMTP();
        $mail->Host       = $ACE_MAIL['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $ACE_MAIL['username'];
        $mail->Password   = $ACE_MAIL['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $ACE_MAIL['port'];
        // Optional debug
        $mail->SMTPDebug  = (int)(getenv('ACE_MAIL_DEBUG') ?: 0); // 0:none, 2:client/server
        if ($mail->SMTPDebug) {
            $mail->Debugoutput = function ($str, $level) {
                error_log("PHPMailer SMTP [{$level}]: " . trim($str));
            };
        }

        $mail->setFrom($ACE_MAIL['from_email'], $ACE_MAIL['from_name']);
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = 'ACE Inquiry Verification Code - ' . $otp;
        $mail->isHTML(false);

        $body  = "Hello {$toName},\n\n";
        $body .= "Your verification code is: {$otp}\n";
        $body .= "This code will expire in 5 minutes.\n\n";
        $body .= "If you did not request this code, please ignore this email.\n\n";
        $body .= "Best regards,\n";
        $body .= "ACE Team\n";
        $body .= "Universiti Kuala Lumpur RCMP";
        $mail->Body = $body;

        if ($mail->send()) {
            return ['ok' => true];
        }
        return ['ok' => false, 'error' => $mail->ErrorInfo ?: 'Unknown mail error'];
    } catch (Exception $e) {
        error_log("Email Error (OTP): " . $e->getMessage());
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}
?>