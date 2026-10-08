<?php

$clientIP = '0.0.0.0';

if (isset($_SERVER['HTTP_CLIENT_IP'])) {
    $clientIP = $_SERVER['HTTP_CLIENT_IP'];
} elseif(isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    # when behind cloudflare
    $clientIP = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif(isset($_SERVER['HTTP_X_FORWARDED'])) {
    $clientIP = $_SERVER['HTTP_X_FORWARDED'];
} elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
    $clientIP = $_SERVER['HTTP_FORWARDED_FOR'];
} elseif(isset($_SERVER['HTTP_FORWARDED'])) {
    $clientIP = $_SERVER['HTTP_FORWARDED'];
} elseif(isset($_SERVER['REMOTE_ADDR'])) {
    $clientIP = $_SERVER['REMOTE_ADDR'];
}

$lat = 51.509865;
$lon = 0.118092;
$city = 'London';

$weatherLookup = array(
    0  => "Clear sky",
    1  => "Mainly clear",
    2  => "Partly cloudy",
    3  => "Overcast",
    45 => "Fog",
    48 => "Depositing rime fog",
    51 => "Light drizzle",
    53 => "Moderate drizzle",
    55 => "Dense drizzle",
    61 => "Slight rain",
    63 => "Moderate rain",
    65 => "Heavy rain",
    71 => "Slight snowfall",
    73 => "Moderate snowfall",
    75 => "Heavy snowfall",
    95 => "Thunderstorm",
    96 => "Thunderstorm with slight hail",
    99 => "Thunderstorm with heavy hail"
);

    $apiurl = 'http://ip-api.com/json/'.$clientIP;
	$data = file_get_contents($apiurl);
	$locObj = json_decode($data);
	$city = $locObj->city;
    $lat = $locObj->lat;
    $lon = $locObj->lon;
	$myTz = $locObj->timezone;

	$dateTimeUTC=date_create("now",timezone_open("UTC"));
	$tz_offset = timezone_offset_get($myTz,$dateTimeUTC);

	$apiurl = 'https://api.open-meteo.com/v1/forecast?latitude='.$lat.'&longitude='.$lon.'&daily=weather_code,temperature_2m_max,temperature_2m_min,sunrise,sunset&models=ukmo_seamless&forecast_days=7';
	$data = file_get_contents($apiurl);
	$weatherObj = json_decode($data);
    $output = ''.$myTZ;
    $output .= '<div class="weather_forecast">';
    $output .= '<div class="weather_today">';
    $output .= '<div class="weather_today_left">';
    $output .= '<div class="weather_city">'.$city.'</div>';
    $d = date_create($weatherObj->daily->time[0], timezone_open('UTC'));
    $output .= '<div class="weather_day">'.date_format($d, 'l').'</div>';
    $output .= '<div class="weather_date">'.date_format($d, 'j').' '.date_format($d, 'F').'</div>';
    $sr = date_create($weatherObj->daily->sunrise[0], timezone_open('UTC'));
    $output .= '<div class="weather_sunrise"><img class="weather_sunrise_img" src="/weather_widget/images/weather-sunrise.png"> '.date_format($sr, 'H:i').'</div>';
    $ss = date_create($weatherObj->daily->sunset[0], timezone_open('UTC'));
    $output .= '<div class="weather_sunset"><img class="weather_sunset_img" src="/weather_widget/images/weather-sunset.png"> '.date_format($ss, 'H:i').'</div>';
    $output .= '</div><div class="weather_today_right" style="background-image: url(/weather_widget/images/wc_'.$weatherObj->daily->weather_code[0].'.png);">';
    $output .= '<div class="weather_summary">'.$weatherLookup[$weatherObj->daily->weather_code[0]].'</div>';
    $output .= '<div class="weather_maxtemp">'.$weatherObj->daily->temperature_2m_max[0].substr($weatherObj->daily_units->temperature_2m_max, 1).'</div>';
    $output .= '<div class="weather_mintemp">'.$weatherObj->daily->temperature_2m_min[0].substr($weatherObj->daily_units->temperature_2m_max, 1).'</div>';
    $output .= '</div></div>';
    $output .= '<div class="weather_outlook">';
	for ($i = 1; $i < 7; $i++) {
        $output .= '<div class="weather_outlook_panel">';
        $d = date_create($weatherObj->daily->time[$i]);
        $output .= '<div class="weather_outlook_panel_left">';
        $output .= '<div class="weather_outlook_day">'.date_format($d, 'D j').'</div>';
        $sr = date_create($weatherObj->daily->sunrise[$i].'Z');
        $output .= '<div class="weather_outlook_sunrise"><img class="weather_sunrise_img" src="/weather_widget/images/weather-sunrise.png"> '.date_format($sr, 'H:i').'</div>';
        $ss = date_create($weatherObj->daily->sunset[$i].'Z');
        $output .= '<div class="weather_outlook_sunset"><img class="weather_sunset_img" src="/weather_widget/images/weather-sunset.png"> '.date_format($ss, 'H:i').'</div>';
        $output .= '</div><div class="weather_outlook_panel_right" style="background-image: url(/weather_widget/images/wc_'.$weatherObj->daily->weather_code[$i].'.png);">';
        $output .= '<div class="weather_outlook_summary">'.$weatherLookup[$weatherObj->daily->weather_code[$i]].'</div>';
        $output .= '<div class="weather_outlook_max">'.$weatherObj->daily->temperature_2m_max[$i].substr($weatherObj->daily_units->temperature_2m_max, 1).'</div>';
        $output .= '<div class="weather_outlook_min">'.$weatherObj->daily->temperature_2m_min[$i].substr($weatherObj->daily_units->temperature_2m_min, 1).'</div>';
        $output .= '</div></div>';
    }
    $output .= '</div>';
    $output .= '</div>';
	echo($output);
?>
