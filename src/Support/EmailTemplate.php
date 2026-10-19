<?php

namespace Dkbs\LaravelDkbsign\Support;

readonly class EmailTemplate
{
    public function __construct(
        public string $signingUrl,
    ) {}

    /**
     * HTML body for POST api/notifications/email.
     */
    public function toBody(): string
    {
        $url = htmlspecialchars($this->signingUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return '<p>Please sign your document using the link below:</p>'
            .'<p><a href="'.$url.'">'.$url.'</a></p>';
    }
}
