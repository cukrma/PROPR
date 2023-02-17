<?php

namespace App\Http\Livewire;

use App\Models\Child;
use App\Models\User;
use AuthorizesRequests;
use Illuminate\View\Component;
use LivewireUI\Modal\ModalComponent;
use App\Traits\EmailConfig;


class LogChild extends ModalComponent
{
    use EmailConfig;

    public Child $child;
    public $showModal = false;

    public function mount(Child $child)
    {
        $this->child = $child;
    }

    public function update()
    {

        $attributes['status'] = "Čeká na schválení";

        $this->child->update($attributes);

        $data = [
            'type' => "applicationSend",
            'subject' => "Potvrzení přijetí závazné přihlášky",
            'child' => $this->child
        ];
        $this->sendEmailToParentOfChild($data);

        $this->closeModalWithEvents([redirect('/user/dashboard')->with('success', 'Přihláška byla odeslána')]);
    }

    public function render()
    {
        return view('livewire.log-child');
    }
}
