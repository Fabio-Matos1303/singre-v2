<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\ServiceOrder;

class ServiceOrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public ServiceOrder $order)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ordem de Serviço #'.$this->order->id.' criada',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.service_order_created',
            with: [
                'order' => $this->order,
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
        // Render the same HTML used by the PDF route and attach as PDF
        $html = view('pdf.service_order', ['order' => $this->order->load(['client','products','services'])])->render();
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf = $dompdf->output();

        $filename = 'service_order_'.$this->order->id.'.pdf';

        return [
            Attachment::fromData(fn () => $pdf, $filename)
                ->as($filename)
                ->withMime('application/pdf'),
        ];
    }
}
