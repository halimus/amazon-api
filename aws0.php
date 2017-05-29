<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'config.php';


class aws {
    /*
     * 
     */
    
    var $conn;
    //var $servername, $username, $password, $database;
    var $servername;
    var $username;
    var $password;
    var $database;
    
    public function __construct() {

//        $servername = "localhost";
//        $username = "root";
//        $password = "";
//        $database = "advertising_db";

        // Create connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->database);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        //echo "Connected successfully";
        
        
        
    }
    
    private function connect_to_db() {
        // Create connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->database);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }
    
    /*
     * 
     */

    public function get_data_from_db() {
        
        $this->connect_to_db();
        
        //echo 'get data from db';
        $sql = "select * from product";
        $result  = mysqli_query($this->conn, $sql);
        
        $rows = array();
        while($row  = mysqli_fetch_array($result)) {
          $rows[] = $row;
        }
        
        mysqli_close($this->conn);
        
        //echo json_encode($rows);
        echo json_encode($rows);
        
    }

    /*
     * 
     */

    public function call_aws($keyword) {
        
        $response = array();
        
        $aws_associate_id = "q0d9b-20";       
        // Your AWS Access Key ID, as taken from the AWS Your Account page
        $aws_access_key_id = "AKIAIOWFZ4KTTJAKNLFQ";

        // Your AWS Secret Key corresponding to the above ID, as taken from the AWS Your Account page
        $aws_secret_key = "DL6rUpqfXpMuQEVmiGGYgudKa0ePlbaR8OX4OjHB";

        // The region you are interested in
        $endpoint = "webservices.amazon.com";

        $uri = "/onca/xml";
        
        //$keyword = 'iphone 7';

        $params = array(
            "Service" => "AWSECommerceService",
            "AWSAccessKeyId" => "$aws_access_key_id",
            "AssociateTag" => "$aws_associate_id",
            "Timestamp" => gmdate('Y-m-d\TH:i:s\Z'),
            
            "Operation" => "ItemSearch",
            "Condition" => "All",
            "ResponseGroup" => "ItemAttributes",
            "SearchIndex" => "All",   // Electronics, MobileApps, Shoes, Software,...
            "Keywords" => "$keyword",
            //"ResponseGroup" => "Images,ItemAttributes,Offers",
        );
        
        // Sort the parameters by key
        ksort($params);
        
        $pairs = array();

        foreach ($params as $key => $value) {
            array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
        }

        // Generate the canonical query
        $canonical_query_string = join("&", $pairs);

        // Generate the string to be signed
        $string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;

        // Generate the signature required by the Product Advertising API
        $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $aws_secret_key, true));

        // Generate the signed URL
        $request_url = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);
        
        /* I prefer using CURL */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        $xml_response = curl_exec($ch);
        if ($xml_response === False){
            $response['error'] = 'There is no response';
        }
        else{
           /* parse XML */
           $parsed_xml = @simplexml_load_string($xml_response);
        }

        $TotalResults =  $parsed_xml->Items->TotalResults;
        if($TotalResults >0){
            $response['ASIN'] = $parsed_xml->Items->Item->ASIN;
            $response['Title'] = $parsed_xml->Items->Item->ItemAttributes->Title;
            $response['MPN']   =   $parsed_xml->Items->Item->ItemAttributes->MPN;
            $response['Price'] = $parsed_xml->Items->Item->ItemAttributes->ListPrice->FormattedPrice; 
        }
        
        echo json_encode($response);
    }

    /*
     * 
     */

    public function insert_data() {
        $response['error'] ='';
        
        $asin = addslashes ($_POST['asin']);
        $title = addslashes ($_POST['title']);
        $mpn = addslashes ($_POST['mpn']);
        $price = addslashes ($_POST['price']);
        
        $sql = "INSERT INTO product (ASIN, Title, MPN, Price) VALUES ('$asin', '$title', '$mpn', '$price')";
        
        if ($this->conn->query($sql) === TRUE) { //Created successfully
            
        } 
        else { 
            $response['error'] = $sql.' - '.$this->conn->error;
        }
        
        echo json_encode($response);
        
    }

}


$aws = new aws();

echo 'ccccc='.$servername;



if(isset($_GET['action']) and $_GET['action']=='get_data'){  
    $aws->get_data_from_db();
}

elseif(isset($_POST)){
    
    if(isset($_POST['keyword'])){
        $keyword = trim($_POST['keyword']);
        $aws->call_aws($keyword);
    }
    else{
        $aws->insert_data();
    }
    
}
