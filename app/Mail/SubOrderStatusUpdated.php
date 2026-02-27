<?php

namespace App\Mail;

use App\Models\SubOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubOrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public SubOrder $subOrder;
    public string $previousStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(SubOrder $subOrder, string $previousStatus = '')
    {
        $this->subOrder = $subOrder;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusLabel = ucwords(str_replace('_', ' ', $this->subOrder->status));
        
        return new Envelope(
            subject: "Order {$statusLabel} - {$this->subOrder->sub_order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sub-order-status-updated',
            with: [
                'subOrder' => $this->subOrder,
                'previousStatus' => $this->previousStatus,
            ],
        );
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
