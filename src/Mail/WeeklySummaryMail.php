<?php

namespace Nafiur\WebAnalytics\Mail;

use Illuminate\Mail\Mailable;

class WeeklySummaryMail extends Mailable
{
    public function __construct(public string $bodyText)
    {
    }

    public function build(): self
    {
        return $this->subject('Laravel Web Analytics — Weekly Summary')
            ->view('web-analytics::mail.weekly-summary');
    }
}
