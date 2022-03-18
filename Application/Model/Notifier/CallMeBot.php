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

    /**
     * @param $message
     * @param $price
     * @param $stations
     *
     * @return bool
     */
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
            curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return (bool) $status;
        } else {
            return false;
        }
    }
}