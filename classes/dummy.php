<?php


if ($persoon or ($values['mailNaarDeelnemers'] == "1")) {

    if ($values['mailNaarDeelnemers'] == "1") {

        if (SSP_ema::MailMeetingVerslag($meeting, '*DEELNEMERS') == true)
            $message = 'Mail verstuurd naar alle deelnemers';

    }

    if (SSP_ema::MailMeetingVerslag($meeting, $persoon) == true)
        $message = 'Mail verstuurd naar ' . $persoon;
    else
        $message = 'Mail NIET verstuurd naar ' . $persoon;

}
