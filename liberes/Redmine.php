<?php
class Redmine {

    public static $login = 'bot';
    public static $password = '*******';
    public static $ip = "85.209.11.253";

    private $cache = null;

    public function setCache($redis) {
	if(is_null($this->cache))
        	$this->cache = $redis;

	return $this;
    }

    public function getUrl() {
        return 'http://' . static::$login . ':' . static::$password . '@' . static::$ip . '/';
    }

    private function getProjects() {

        if (!$this->cache->exists('bot_projects')) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->getUrl() . 'projects.json',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
               
            ));
    
            $response = curl_exec($curl);
    
            curl_close($curl);


            $this->cache->set('bot_projects', $response, 3600 * 24);
        }
        
        return json_decode($this->cache->get('bot_projects'));
    }

    public function getProjectIdByName($name) {
        $projects = $this->getProjects();
        
        foreach($projects->projects as $items) {
            if ($items->name == $name) {
                $projectId = $items->id;
            }
        }

        return $projectId;
    }

    public function issue($projectId, $params = []) {
        $curl = curl_init();

        $issue = [
            'issue' => [
                'project_id' => $projectId,
                //'subject' => $params['subject'],
            ]
        ];

        if (!empty($params['subject'])) {
            $issue['issue']['subject'] = $params['subject'];
        }

        if (!empty($params['issue_id'])) {
            $issue['issue']['issue_id'] = $params['issue_id'];
        }

        if (!empty($params['include']['attachments'])) {
            $issue['issue']['include']['attachments'] = $params['include']['attachments'];
        }

        if (!empty($params['description'])) {
            $issue['issue']['description'] = $params['description'];
        }

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->getUrl() . 'issues.json',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => json_encode($issue),
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);

        $response = json_decode($response, true);

        return $response;
    }

    public function updateIssue($projectId, $issueId, $comment) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getUrl() . 'issues/' . $issueId . '.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS =>'{
            "issue": {
                "project_id": "' . $projectId . '",
                "subject": "Добавил переписку",
                "notes": "' . $comment . '"
            }
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: _redmine_session=OHozQUw1dWpoNUxCZmV1bG4vK2NaUGtxaWhOcE5tV0Q2K2Z3ejNpY3p2NURIZXJ4bzFGRGU5QkMwUjFsN3MzdzYxcTdJdDY0WUpmN1h1YUpBREJHQjQrcHgrUnNTeW1lTnVsb2ozUWxSSUxlY01XNzNESnBTQzIwbzloYlhlOFMvQTBzb1g3RVNSUVN6aXdDelJEcjRxUXFKdG9LZFYxV01IREQ2dm5ZN0hLSlVPTXJwZ0wrU0h2UWlVZThNVmNJNW92bFpqMm9EM1lGTnBhTFZ3d1F5K09WK0RIT0FTSEd0NkkzRzgyQkxSZz0tLUlIdXJCKzZmdUNsYTV4R29YOG5tQ1E9PQ%3D%3D--d152d1e8898f47cbe25c30f018135ce52ad221a2'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

    }
}
