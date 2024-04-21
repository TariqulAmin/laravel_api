<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use App\Jobs\SendEmail;

class AuthController extends Controller
{
    use HttpResponses;

    private $email;
    private $name;
    private $client_id;
    private $client_secret;
    private $token;
    private $provider;

    /**
     * Default Constructor
     */
    public function __construct()
    {
        $this->email            = 'sajibcuet10@gmail.com';
        $this->name             = 'A. B. M. Tariqul Amin';
        $this->client_id        = env('GMAIL_API_CLIENT_ID');
        $this->client_secret    = env('GMAIL_API_CLIENT_SECRET');
        $this->token            = env('GMAIL_API_REFRESH_TOKEN');

        $this->provider         = new Google(
            [
                'clientId'      => $this->client_id,
                'clientSecret'  => $this->client_secret
            ]
        );
    }

    private function sendEmail($user_email, $user_name, $user_provider)
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
            $mail->addAddress($user_email, $user_name);
            $mail->Subject = 'Thanks for registration';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $body = 'Welcome <b>' . $user_name . '</b>,<br><br>Yo have successfully completed registration in our Laravel Project with Gmail OAuth2.<br><br>Thank you,<br><b>' . $this->name . '</b>';
            $mail->msgHTML($body);
            $mail->AltBody = 'This is a plain text message body';
            if ($mail->send()) {
                return redirect()->back()->with('success', 'Successfully send email!');
            } else {
                return redirect()->back()->with('error', 'Unable to send email.');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Exception: ' . $e->getMessage());
        }
    }

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $data['user'] =  $user;
            /** @var \App\Models\User $user **/
            $data['token'] =  $user->createToken('laravelRegistration')->plainTextToken;

            return $this->success($data, 'User loggedin successfully.');
        } else {
            return $this->error(['error' => 'Credentials do not match'], 'Unauthorised.', 401);
        }
    }

    public function register(RegisterUserRequest $request)
    {
        $request->validated($request->all());

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        /** @var \App\Models\User $user */
        $token =  $user->createToken('laravelRegistration')->plainTextToken;
        $data["user"] = $user;
        $data["token"] = $token;

        // return $this->sendEmail($request->email, $request->name, $this->provider);
        // Mail::to($user->email)->send(new RegistrationMail($user->email, $user->name, $this->client_id, $this->client_secret, $this->token, $this->provider));



        SendEmail::dispatch($request->email, $request->name, $this->client_id, $this->client_secret, $this->token, json_encode($this->provider))->onQueue('emails');

        return $this->success($this->provider, 'User registered successfully.');
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return $this->success("", 'User signed out successfully');
    }
}
