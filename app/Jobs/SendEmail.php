<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use League\OAuth2\Client\Provider\Google;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $name;
    protected $client_id;
    protected $client_secret;
    protected $token;
    protected $provider;

    /**
     * Create a new job instance.
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
     * Execute the job.
     */
    public function handle(): void
    {
        // $provider = new Google([
        //     'clientId'      => $this->client_id,
        //     'clientSecret'  => $this->client_secret
        // ]);


        $mail = new PHPMailer(true);

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
                    // 'provider'          => $this->provider,
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
        $mail->send();
    }
}
