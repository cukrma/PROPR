<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\EmailConfig;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use EmailConfig;

    public function create()
    {
        return view('register.create');
    }

    public function store()
    {
        //create user

        $attributes = request()->validate([
            'name' => 'required|max:255',
            'username' => 'required|max:255|min:3|unique:users,username',
            'email' => 'required|email|min:7|max:255|unique:users,email',
            'password' => 'required|min:7|max:255',
            'password_confirm' => 'required|same:password'
        ]);

        $withoutPasswordConfirm = array_slice($attributes, 0, 4);
        $user = User::create($withoutPasswordConfirm);

        auth()->login($user);

        //jedna moznost
        //session()->flash('success', 'Your account has been created.');

        $data = [
            'type' => "register",
            'subject' => "Registrace na webu Tábor Markrabka",
        ];
        $response = $this->createEmail($data);

        $this->sendEmailTo($response['email'], $attributes['email']);

        return redirect('/')->with('success', 'Váš účet byl vytvořen.');
    }
}
