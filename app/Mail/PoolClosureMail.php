<?php

namespace App\Mail;

use App\Models\PoolClosure;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PoolClosureMail extends Mailable
{
    use Queueable, SerializesModels;

    public $poolClosure;

    public $impactedItems;

    public function __construct(PoolClosure $poolClosure, array $impactedItems = [])
    {
        $this->poolClosure = $poolClosure;
        $this->impactedItems = $impactedItems;
    }

    public function build()
    {
        $this->poolClosure->loadMissing(['complexActivity.complex', 'complexActivity.activity']);

        $complexName = optional($this->poolClosure->complexActivity->complex)->nom
            ?? optional($this->poolClosure->complexActivity)->complex_id
            ?? '—';

        return $this
            ->subject('إشعار تعطل المسبح: ' . $complexName)
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.pool-closure');
    }
}
