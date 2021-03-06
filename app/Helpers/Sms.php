<?php

class Sms
{
    public $api_url = 'http://www.thsms.com/api/rest';
    public $username = null;
    public $password = null;

    public function getCredit()
    {
        $params['method'] = 'credit';
        $params['username'] = $this->username;
        $params['password'] = $this->password;

        $result = $this->curl($params);

        $xml = @simplexml_load_string($result);

        if (!is_object($xml)) {
            return array(false, 'Respond error');

        } else {

            if ($xml->credit->status == 'success') {
                return array(true, $xml->credit->status);
            } else {
                return array(false, $xml->credit->message);
            }
        }
    }

    public function send($from = '0000', $to = null, $message = null)
    {
        $params['method'] = 'send';
        $params['username'] = $this->username;
        $params['password'] = $this->password;

        $params['from'] = $from;
        $params['to'] = $to;
        $params['message'] = $message;

        if (is_null($params['to']) || is_null($params['message'])) {
            return false;
        }

        $result = $this->curl($params);
        $xml = @simplexml_load_string($result);
        if (!is_object($xml)) {
            return array(false, 'Respond error');
        } else {
            if ($xml->send->status == 'success') {
                return array(true, $xml->send->uuid);
            } else {
                return array(false, $xml->send->message);
            }
        }
    }

    public function generateNumericOTP($n)
    {

        $generator = "1357902468";

        $result = "";

        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand() % (strlen($generator))), 1);
        }

        return $result;
    }

    private function curl($params = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $lastError = curl_error($ch);
        $lastReq = curl_getinfo($ch);
        curl_close($ch);

        return $response;
    }

}
