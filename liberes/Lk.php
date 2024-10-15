<?php
class Lk {
    private $token = "3E6U1s7zEqGnFQOjFcha0sfa6Z9Ls0U8NOT6BQbN";
    private $url = "https://my.31337.ru/api/v1/tgbot/";

    public function getUser($username)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url . 'user',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"username": "' . $username . '"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
                'Cookie: bearhost_underground_session=eyJpdiI6InM3MXdHRGZtU2ExcFpDOGtjVC8wZUE9PSIsInZhbHVlIjoiQmNiU1RqYWdnajBueVRxeHQzTlF0UkpDSWZ6eTJYZFdnV3ZPUi95ZDczK0l5SmhQSnExMzRPcTZLWVhUdHlLUTJCby90b2IxakZJWXpvZWlBZzlBSEZ5N3NzZitXTnk4dm83RXFyeFVhdkQ3Z1hDNWp4VElseFhpTnY2amZmQ0siLCJtYWMiOiI4Mzc0YTQxNTZjZDUwOGVmMDcyNWFkNTJiZmY2OTBjZTdhOTYyZDRkOWJiYTJkMmQzODg4M2M2YzM4YjhjZmY2IiwidGFnIjoiIn0%3D'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);

    }

    public function serverReboot($serverId, $userId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url . "server/reboot?server_id={$serverId}&user_id={$userId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->token,
                'Cookie: bearhost_underground_session=eyJpdiI6Im5JdDdyajFOVUVZUGpsSXRzVjg3Wnc9PSIsInZhbHVlIjoiOTR3ZCtYUVNEeTJPUFIwaXd4VWZWVSt1amFJTWpOdTRnSmhhREpuUkkyTEFCbzNUY3JtWHJ6NlhqVHNOamhvRUVKcVNpa2hlMmpqVWlvVU9OSUJFcVNlTnQxY0pPdzVLUDg4MzZ4WXEzejgwd1poUnNESUlZTVhiUGxTMmdWMWUiLCJtYWMiOiI5YzhiZjk0YjAwNTk4NTdiYjk2ZmFjMGU3MTgyNDUwZWViNTQxOWNkN2FmMjE2YjcyOTAwZjRjN2RiNmY3OTRkIiwidGFnIjoiIn0%3D'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    public function serverOsInstall($params)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url . 'server/os_install',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->token,
                'Cookie: bearhost_underground_session=eyJpdiI6IkpDbUhKWjl2ZUl1eHdxaGtBRFRjdlE9PSIsInZhbHVlIjoiRmtXSjlLU3VIbTNlTTFpN09vZUEvMkVNYXFaTWxLY1BrOVNBSHhOVjZlVk9RclRTOHIxZHF4Uzg2MTNjaVVBNCswUk5RZnhlei9SNTZQSk5TZ3N1VzNERVgvb29UcHZqbEdUcE5QMlgzZC9FK09pem5oSERlQ29iellpdGt1Nm4iLCJtYWMiOiI4MmVmOThmOGZhMWNiNWYwNTFiZjhkZDk5NmQwYjc1MTM0ZTAxMmI1ZTNhMGU4YjU1ZjNjODVjYjczNjk5MTUxIiwidGFnIjoiIn0%3D'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    public function userChat($params)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url . '/user/chat',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('user_id' => '1','chat_id' => '1','author' => 'test','text' => 'test'),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->token,
                'Cookie: bearhost_underground_session=eyJpdiI6InpkNmpiK0ZBOTJVcDlLU280Wi9WN2c9PSIsInZhbHVlIjoiV2NWbzh2VkV3NzZXdUplTjVkME92MG1OVDVGZmRtSTVvcURsS2kxWFhEQ29UNXpCdnlSeGRNNXVSOHhveWhrNS9haU83Y2VzbTJkYStvR0RiNDMrTUJrUGIrc0VuYzZST0JLcUpuMWRBeXNrdEtYQnRTYzMrT0ZOeXp6cm9IM1AiLCJtYWMiOiI0ZTUyYmUzNTY3ZWY3NTAzNmFmM2FmMTQ1NDQzMzQxZDM1MjIxZjQ5MTZkYjA2NDYxZjRhNjRlOWUzY2Y4ZjRmIiwidGFnIjoiIn0%3D'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }
}
?>