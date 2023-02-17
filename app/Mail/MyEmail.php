<?php

namespace App\Mail;

use App\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailables\Content;


// trida samotneho vlastniho emailu
class MyEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    private $data;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Email  $MyEmail
     * @return void
     */
    public function __construct($data) // Email $MyEmail
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view_type = 'emails.' . $this->data['type'];

        return $this->subject($this->data['subject'])
            ->view($view_type)->with('data', $this->data);
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    // public function content()
    // {
    //     return new Content(
    //         view: 'emails.orders.shipped',
    //     );
    // }
}
