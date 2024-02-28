<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class OrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param Order $order
     */
    public function __construct(
        public Order $order
    )
    {
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Electronics Parts Order Received - '.siteName(),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.order-created',
        );
    }

    public function build()
    {
        return $this
            ->replyTo($this->order->user->email,$this->order->user->name);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        if($this->order->orderFiles()->count())
        {
            $files = [];
            foreach($this->order->orderFiles as $key => $file)
            {
                $files[] = Attachment::fromPath(\Storage::disk('public')->path(config('constants.BOM_FILE_PATH').$file->file_name))->as($file->file_name);
            }
            return $files;
        } else {
            return [];
        }
    }
}
