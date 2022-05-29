<?php

function getLocationInfoByIp(){
    /*
        * current-php-code 2021-jan-17
        * internal-call
        * input-sanitized : lang
        */
    $client  = @$_SERVER['HTTP_CLIENT_IP'];

    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];

    $remote  = @$_SERVER['REMOTE_ADDR'];

    $result  = array('country'=>'', 'city'=>'');

    if(filter_var($client, FILTER_VALIDATE_IP)){

        $ip = $client;

    }elseif(filter_var($forward, FILTER_VALIDATE_IP)){

        $ip = $forward;

    }else{

        $ip = $remote;

    }

    $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));

    //print_r($ip_data); exit;

    if($ip_data && $ip_data->geoplugin_countryName != null){

        $result['country'] = $ip_data->geoplugin_countryName;

        $result['city'] = $ip_data->geoplugin_city;

        $result['latitude'] = $ip_data->geoplugin_latitude;

        $result['longitude'] = $ip_data->geoplugin_longitude;

    }

    /* $country = $result['country'];

     $geocode_stats = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address='".$country."'&sensor=false");

     $output_deals = json_decode($geocode_stats);

     $latLng = $output_deals->results[0]->geometry->location;

     $result['lat'] = $latLng->lat;

     $result['lng'] = $latLng->lng;   */

    return $result;

}