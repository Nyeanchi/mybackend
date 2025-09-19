<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    public function __construct($user, $password = null)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Welcome to Domotena, ' . $this->user->first_name . '!')
            ->view('emails.welcome')
            ->with([
                'user' => $this->user,
                'password' => $this->password,
            ]);
    }
}




// namespace App\Mail;

// use Illuminate\Bus\Queueable;
// use Illuminate\Mail\Mailable;
// use Illuminate\Queue\SerializesModels;

// class WelcomeEmail extends Mailable
// {
//     use Queueable, SerializesModels;

//     public $user;

//     public function __construct($user)
//     {
//         $this->user = $user;
//     }

//     public function build()
//     {
//         return $this->from(config('mail.from.address'), config('mail.from.name'))
//                     ->subject('Welcome to Domotena, ' . $this->user->first_name . '!')
//                     ->view('emails.welcome')
//                     ->with(['user' => $this->user]);
//     }
// }
