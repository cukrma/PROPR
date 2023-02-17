<?php

namespace App\Traits;

use App\Models\Email;
use App\Models\User;
use App\Mail\MyEmail;
use Illuminate\Support\Facades\Mail;
use Exception;
use Monolog\Registry;

trait EmailConfig
{
    // funkce pro vytvoreni emailu daneho typu (registrace, zavazna prihlaska, potvrzeni platby, ...)
    public function createEmail($data)
    {
        $result = [
            'email' => new MyEmail($data),
        ];

        return $result;
    }

    // funkce, ktera zasle jiz vytvoreny email konkretnimu prijemci ci skupine prijemcu (pak je vstupem pole prijemcu)
    public function sendEmailTo($email, $recipient)
    {
        try {
            if (is_array($recipient)) {
                Mail::bcc($recipient)->send($email);
            }
            else {
                Mail::to($recipient)->send($email);
            }
        }
        catch (Exception $e) {
            return response()->json([$e->getMessage()]);
        }  
    }

    // funkce, ktera vytvori email a posle ho rodici ditete
    public function sendEmailToParentOfChild($data)
    {
        $response = $this->createEmail($data);
        $user_id = $data['child']->user_id;
        $user_email = User::firstWhere('id', $user_id)->email;

        $this->sendEmailTo($response['email'], $user_email);
    }

}

