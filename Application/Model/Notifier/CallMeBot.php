<?php

namespace Daniels\Benzinlogger\Application\Model\Notifier;

/**
 * https://www.callmebot.com/
 */
class CallMeBot extends AbstractNotifier implements NotifierInterface
{
    public $phoneNumber;
    public $apiKey;

    public function __construct($phoneNumber, $apiKey)
    {
        $this->phoneNumber = $phoneNumber;
        $this->apiKey = $apiKey;
    }

    public function notify($message, $price, $stations)
    {
        if (false === $this->getTimeControl()->availableAtTheMoment()) {
            return false;
        }

        $message .= ' '.$price.' '.$stations;

        $url='https://api.callmebot.com/whatsapp.php?source=php&phone='.$this->phoneNumber.'&text='.urlencode($message).'&apikey='.$this->apiKey;

        if($ch = curl_init($url))
        {
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $html = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            //echo "Output:".$html;  // you can print the output for troubleshooting
            curl_close($ch);
            return (int) $status;
        } else {
            return false;
        }
    }
}