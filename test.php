<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>

    <?php
    
        $aws_associate_id = "q0d9b-20";
        
        // Your AWS Access Key ID, as taken from the AWS Your Account page
        $aws_access_key_id = "AKIAIOWFZ4KTTJAKNLFQ";

        // Your AWS Secret Key corresponding to the above ID, as taken from the AWS Your Account page
        $aws_secret_key = "DL6rUpqfXpMuQEVmiGGYgudKa0ePlbaR8OX4OjHB";

        // The region you are interested in
        $endpoint = "webservices.amazon.com";

        $uri = "/onca/xml";
        
        $keywords = 'playstation 4';

        $params = array(
            "Service" => "AWSECommerceService",
            "AWSAccessKeyId" => "$aws_access_key_id",
            "AssociateTag" => "$aws_associate_id",
            "Timestamp" => gmdate('Y-m-d\TH:i:s\Z'),
            
            "Operation" => "ItemSearch",
            "Condition" => "All",
            "ResponseGroup" => "ItemAttributes",
            "SearchIndex" => "All",   // Electronics, MobileApps, Shoes, Software,...
            "Keywords" => "$keywords",
            //"ResponseGroup" => "Images,ItemAttributes,Offers",
        );
        
//          Service=AWSECommerceService
//  &Operation=ItemLookup
//  &ResponseGroup=Offers
//  &IdType=ASIN
//  &ItemId=B00KOKTZLQ
//  &AssociateTag=[Your_AssociateTag]
//  &AWSAccessKeyId=[Your_AWSAccessKeyId]
//  &Timestamp=[YYYY-MM-DDThh:mm:ssZ]
//  &Signature=[Request_Signature]
        
        $keywords = 'nike';
        
        $params0 = array(
            "Service" => "AWSECommerceService",
            "Operation" => "ItemSearch",
            "ResponseGroup" => "Offers",
            //"IdType" => "ASIN",
            "SearchIndex" => "All",
            "Keywords" => $keywords,
            "AWSAccessKeyId" => "$aws_access_key_id",
            "AssociateTag" => "$aws_associate_id",
            "Timestamp" => gmdate('Y-m-d\TH:i:s\Z'),
            //"ResponseGroup" => "Images,ItemAttributes,Offers",
            //"Sort" => "price"
        );
        
        //&IdType=ASIN
       // &ItemId=B00KOKTZLQ
        
      //  [Message] => Your request should have atleast 1 of the following parameters: 'Keywords','Title','Power','BrowseNode',
      //  'Artist','Author','Actor','Director','AudienceRating','Manufacturer','MusicLabel','Composer','Publisher','Brand',
      //  'Conductor','Orchestra','TextStream','Cuisine','City','Neighborhood'.

        //echo '<pre>';print_r($params);echo '</pre>';
        // Sort the parameters by key
       ksort($params);
        
        //echo '<hr><pre>';print_r($params);echo '</pre>';
       // exit;

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

        //echo "Signed URL: \"".$request_url."\"";
       // echo $request_url;
        
        /* I prefer using CURL */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        $xml_response = curl_exec($ch);
        if ($xml_response === False){
            echo 'return false';
        }
        else{
        /* parse XML */
           $parsed_xml = @simplexml_load_string($xml_response);
          //return ($parsed_xml === False) ? False : $parsed_xml;
        }
        
       
        echo '<pre>'; print_r($parsed_xml); '</pre>'; // exit;
    
        $TotalResults =  $parsed_xml->Items->TotalResults;
        echo 't='.$TotalResults;
        if($TotalResults >0){
            echo '<hr>';
            echo "ASIN :  {$parsed_xml->Items->Item->ASIN}<br>";
            echo "Title : {$parsed_xml->Items->Item->ItemAttributes->Title}<br>";
            echo "MPN :   {$parsed_xml->Items->Item->ItemAttributes->MPN}<br>";
            echo "Price : {$parsed_xml->Items->Item->ItemAttributes->ListPrice->FormattedPrice}<br>";
        }
        
        ?>
    </body>
</html>
