<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Illuminate\Support\Facades\Mail;

class RegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $name;
    public $client_id;
    public $client_secret;
    public $token;
    public $provider;

    /**
     * Create a new message instance.
     */
    public function __construct($email, $name, $client_id, $client_secret, $token, $provider)
    {
        $this->email = $email;
        $this->name = $name;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->token = $token;
        $this->provider = $provider;
    }

    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Registration Mail',
    //     );
    // }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 465;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->SMTPAuth = true;
            $mail->AuthType = 'XOAUTH2';
            $mail->setOAuth(
                new OAuth(
                    [
                        'provider'          => $this->provider,
                        'clientId'          => $this->client_id,
                        'clientSecret'      => $this->client_secret,
                        'refreshToken'      => $this->token,
                        'userName'          => $this->email
                    ]
                )
            );

            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($this->email, $this->name);
            $mail->Subject = 'Thanks for registration';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $body = 'Welcome <b>' . $this->name . '</b>,<br><br>Yo have successfully completed registration in our Laravel Project with Gmail OAuth2.<br><br>Thank you,<br><b>' . $this->name . '</b>';
            $mail->msgHTML($body);
            $mail->AltBody = 'This is a plain text message body';
            return $mail->send();
            if ($mail->send()) {
                return redirect()->back()->with('success', 'Successfully send email!');
            } else {
                return redirect()->back()->with('error', 'Unable to send email.');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
