<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class EbankEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    public function build()
    {//dd($this->to);
      try{ 
      $to = Arr::get($this->to, '0.address');

      
        return $this->view('emails.test_html')
            ->text('emails.test_text')
            ->subject('Ebank Email')
            ->attachFromStorageDisk('public', 'example.png')
            ->withSymfonyMessage(function ($message) use ($to) {
                $message->getHeaders()->addTextHeader('X-MailerSend-Variables', json_encode([
                    'name' => 'Your Name',
                    'var' => 'variable',
                    'number' => 123,
                ]));
            });
       } catch (\Exception $e) {
        // عرض الخطأ بطريقة مفصلة
        return response()->json([
            'error' => 'An error occurred while building the email.',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
    }
}
