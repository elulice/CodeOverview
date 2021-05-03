<?php

/**
 * Pay per TIC Integration Library
 *
 *
 */
class PPT {

    const version = "2.0";

    private $client_id;
    private $client_secret;
    private $username;
    private $password;
    private $access_token;
    private $access_data;

    function __construct() {
        $i = func_num_args();

        if ($i != 4) {
            throw new Exception("Invalid arguments. Use USERNAME, PASSWORD, CLIENT_ID, and CLIENT SECRET");
        }

        $this->username = func_get_arg(0);
        $this->password = func_get_arg(1);
        $this->client_id = func_get_arg(2);
        $this->client_secret = func_get_arg(3);
    }

    /**
     * Get Access Token for API use
     */
    public function get_access_token() {
        if (isset($this->access_token) && !is_null($this->access_token)) {
            return $this->access_token;
        }

        $url_access_token = 'https://a.paypertic.com/auth/realms/entidades/protocol/openid-connect/token';

        $app_client_values = array(
            'username' => $this->username,
            'password' => $this->password,
            'grant_type' => 'password',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'cache-control' => 'no-cache'
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url_access_token);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($app_client_values));


        $api_result = curl_exec($curl);
        $api_http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($api_result === FALSE) {
            throw new PayperTicException(curl_error($curl));
        }

        $response = array(
            "status" => $api_http_code,
            "response" => json_decode($api_result, true)
        );

        if ($response["status"] != 200) {
            throw new PayperTicException($response['response']['error_description'], $response['status']);
        }

        return $response['response']['access_token'];
    }

    /**
     * Create a checkout preference
     * @param array $preference
     * @return array(json)
     */
    public function create_preference($preference) {
        $request = array(
            "uri" => "/pagos",
            "params" => array(
                "access_token" => $this->get_access_token()
            ),
            "method" => "POST",
            "header" => array('content-type: application/json',
                'cache-control: no-cache'),
            "data" => $preference
        );

        $preference_result = PPTRestClient::post($request);
        return $preference_result;
    }

}

/**
 * Pay per TIC cURL RestClient
 */
class PPTRestClient {

    const API_BASE_URL = "https://api.paypertic.com";

    private static function build_request($request) {
        if (!extension_loaded("curl")) {
            throw new PayperTicException("cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.");
        }

        if (!isset($request["method"])) {
            throw new PayperTicException("No HTTP METHOD specified");
        }

        if (!isset($request["uri"])) {
            throw new PayperTicException("No URI specified");
        }

        // Set headers
        $headers = $request["header"];

        // Build $connect
        $connect = curl_init();

        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_ENCODING, '');
        curl_setopt($connect, CURLOPT_MAXREDIRS, 10);
        curl_setopt($connect, CURLOPT_TIMEOUT, 0);
        curl_setopt($connect, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($connect, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $request["method"]);

        curl_setopt($connect, CURLOPT_HTTPHEADER, $headers);

        // Set parameters and url
        if (isset($request["params"]) && is_array($request["params"]) && count($request["params"]) > 0) {
            $request["uri"] .= (strpos($request["uri"], "?") === false) ? "?" : "&";
            $request["uri"] .= self::build_query($request["params"]);
        }
        curl_setopt($connect, CURLOPT_URL, self::API_BASE_URL . $request["uri"]);

        // Set data
        if (isset($request["data"])) {
            $request["data"] = json_encode($request["data"]);
            if (function_exists('json_last_error')) {
                $json_error = json_last_error();
                if ($json_error != JSON_ERROR_NONE) {
                    echo ("JSON Error [{$json_error}] - Data: " . $request["data"]);
                }
            }
            curl_setopt($connect, CURLOPT_POSTFIELDS, $request["data"]);
        }

        return $connect;
    }

    private static function exec($request) {
// private static function exec($method, $uri, $data, $content_type) {

        $connect = self::build_request($request);

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        if ($api_result === FALSE) {
            echo curl_error($connect);
        }

        $response = array(
            "status" => $api_http_code,
            "response" => json_decode($api_result, true)
        );

        if ($response['status'] >= 400) {
            $message = $response['response']['message'];
            if (isset($response['response']['cause'])) {
                if (isset($response['response']['cause']['code']) && isset($response['response']['cause']['description'])) {
                    $message .= " - " . $response['response']['cause']['code'] . ': ' . $response['response']['cause']['description'];
                } else if (is_array($response['response']['cause'])) {
                    foreach ($response['response']['cause'] as $cause) {
                        $message .= " - " . $cause['code'] . ': ' . $cause['description'];
                    }
                }
            }
//            var_dump($response); // Descomentar para debug.
        }

        curl_close($connect);

        return $response;
    }

    private static function build_query($params) {
        if (function_exists("http_build_query")) {
            return http_build_query($params, "", "&");
        } else {
            foreach ($params as $name => $value) {
                $elements[] = "{$name}=" . urlencode($value);
            }

            return implode("&", $elements);
        }
    }

    public static function get($request) {
        $request["method"] = "GET";

        return self::exec($request);
    }

    public static function post($request) {
        $request["method"] = "POST";

        return self::exec($request);
    }

    public static function put($request) {
        $request["method"] = "PUT";

        return self::exec($request);
    }

    public static function delete($request) {
        $request["method"] = "DELETE";

        return self::exec($request);
    }

}

class PayperTicException extends Exception {

    public function __construct($message, $code = 500, Exception $previous = null) {
// Default code 500
        parent::__construct($message, $code, $previous);
    }

}
