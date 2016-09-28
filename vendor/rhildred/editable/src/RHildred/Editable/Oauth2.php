<?php

namespace RHildred\Editable;

class Oauth2
{
	private $sRedirect = "";
	private $oCreds;
	public function __construct($sReturnUrl)
	{
		$this->oCreds = json_decode(file_get_contents(__DIR__ . '/../../../../../../creds/google.json'));
		$rgxQuery = "/\?.*$/";
		$this->sRedirect = preg_replace($rgxQuery, "", $sReturnUrl);
		if (!preg_match("/localhost/", $this->sRedirect))
		{
			$rgxPort = "/:\d\d*/";
			$this->sRedirect = preg_replace($rgxPort, "", $this->sRedirect);
		}
	}

	public function redirect()
	{
		$auth_url = "https://accounts.google.com/o/oauth2/auth?redirect_uri=" . $this->sRedirect
		 . "&client_id=" . $this->oCreds->ClientID
		 . "&scope=https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email"
		 . "&response_type=code&max_auth_age=0";

        return($auth_url);
	}

	private function run_curl($url, $method = 'GET', $postvals = null){
	    $ch = curl_init($url);

	    //GET request: send headers and return data transfer
	    if ($method == 'GET'){
	        $options = array(
	            CURLOPT_URL => $url,
	            CURLOPT_RETURNTRANSFER => 1,
	        	CURLOPT_SSL_VERIFYPEER => false
	        );
	        curl_setopt_array($ch, $options);
	    //POST / PUT request: send post object and return data transfer
	    } else {
	        $options = array(
	            CURLOPT_URL => $url,
	            CURLOPT_POST => 1,
	            CURLOPT_POSTFIELDS => http_build_query($postvals),
	            CURLOPT_RETURNTRANSFER => 1,
	        	CURLOPT_SSL_VERIFYPEER => false
	        );
	        curl_setopt_array($ch, $options);
	    }
	    if( ! $response = curl_exec($ch))
	    {
	        trigger_error(curl_error($ch));
	    }
	    curl_close($ch);

	    return $response;
	}

	public function handleCode($code)
	{
		$postvals = array('grant_type' => 'authorization_code',
				'client_id' => $this->oCreds->ClientID,
				'client_secret' => $this->oCreds->ClientSecret,
				'code' => $code,
				'redirect_uri' => $this->sRedirect);

		//get JSON access token object (with refresh_token parameter)
		$sReturn = $this->run_curl('https://accounts.google.com/o/oauth2/token', 'POST', $postvals);
		$token = json_decode($sReturn);

		//construct URI to fetch profile for current user
		$profile_url = "https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=" . $token->access_token;

		//fetch profile of current user
		$oProfile = json_decode($this->run_curl($profile_url, 'GET'));

        //check to see if current user is in the list
        foreach($this->oCreds->Users as $sEmail){
            if($sEmail == $oProfile->email)return $oProfile;
        }
		throw new Exception('user not in list');

	}

}

