<?php

/**
 * Copyright (c) 2018-2024 David J Bullock
 * Web Power and Light, LLC
 * https://webpowerandlight.com
 * support@webpowerandlight.com
 *
 */


 class_exists('m4is_r83' )||die();
  final 
class m4is_q6082 {
const DEFAULT_PROVIDER ='query_extreme';
 static 
function m4is_a67($m4is_j45 ='', $m4is_c41 ='' ){
$m4is_j45 =empty($m4is_j45 )? m4is_a01587::m4is_y342(): $m4is_j45;
  $m4is_k256 =['10.', '172.16.', '192.168.', '127.', ];
 foreach($m4is_k256 as $m4is_y0892 ){
if(substr($m4is_j45, 0, strlen($m4is_y0892 ))== $m4is_y0892 ){
return ['city' =>'Local', 'continent' =>'Local', 'country_name' =>'Local', 'countrycode' =>'Local', 'isp' =>'Local', 'latitude' =>'Local', 'longitude' =>'Local', 'region_name' =>'Local', 'timezone' =>'Local', 'postalcode' =>'Local' ];
 
}
} $m4is_d2365 =self::m4is_a68726();
 if($m4is_c41 == 'query_geoip' &&!function_exists('geoip_detect_get_info_from_ip' )){
$m4is_c41 ='';
 
}if(!empty($m4is_c41 )){
if(substr($m4is_c41, 0, 6 )!== 'query_' ){
$m4is_c41 ='query_' . $m4is_c41;
 
}
}$m4is_c41 =empty($m4is_c41 )? self::DEFAULT_PROVIDER : $m4is_c41;
 $m4is_q1046 =self::m4is_b816($m4is_j45, $m4is_c41 );
  $m4is_a9317 =get_transient($m4is_q1046 );
  $m4is_s7349 =$m4is_c41;
 if($m4is_a9317 ){
return $m4is_a9317;
 
}if(method_exists('m4is_q6082', $m4is_s7349)){
$m4is_a9317 =self::$m4is_s7349($m4is_j45 );
 
}if(!empty($m4is_a9317 )){
set_transient($m4is_q1046, $m4is_a9317, MONTH_IN_SECONDS );
 
}return $m4is_a9317;
 
}static 
function m4is_b816($m4is_j45 ='', $m4is_c41 =''){
$m4is_j45 =empty($m4is_j45)? m4is_a01587::m4is_y342(): $m4is_j45;
 $m4is_q1046 ="memberium/geolocation" . (empty($m4is_c41)? '' : "::{$m4is_c41
}"). "::{$m4is_j45
}";
 return $m4is_q1046;
 
}static 
function m4is_a68726(){
return ['query_extreme',  'query_geoip',  'query_hostip',  'query_ipapi',  'query_ipinfodb',  'query_ipstack',  ];
 
}private static 
function m4is_i04(){
return self::DEFAULT_PROVIDER;
 
}   private static 
function query_extreme($m4is_j45 =''){
 $m4is_a9317 =[];
 $m4is_l9671 =defined('EXTREME_IP_LOOKUP_API_KEY' )? constant('EXTREME_IP_LOOKUP_API_KEY' ): '';
 $m4is_i93625 ="https://extreme-ip-lookup.com/json/{$m4is_j45
}";
 if(!empty($m4is_l9671 )){
$m4is_i93625 ="https://extreme-ip-lookup.com/json/{$m4is_j45
}?key={$m4is_l9671
}";
 
}$m4is_l91805 =json_decode(m4is_a01587::m4is_f46823($m4is_i93625 ));
 if(is_object($m4is_l91805)&&property_exists($m4is_l91805, 'status')&&$m4is_l91805->status == 'success'){
$m4is_a9317['city']=$m4is_l91805->city;
 $m4is_a9317['continent']=$m4is_l91805->continent;
 $m4is_a9317['country_name']=$m4is_l91805->country;
 $m4is_a9317['countrycode']=$m4is_l91805->countryCode;
 $m4is_a9317['isp']=$m4is_l91805->isp;
 $m4is_a9317['latitude']=$m4is_l91805->lat;
 $m4is_a9317['longitude']=$m4is_l91805->lon;
 $m4is_a9317['region_name']=$m4is_l91805->region;
 $m4is_a9317['timezone']='';
 $m4is_a9317['postalcode']='';
 
}return $m4is_a9317;
 
}private static 
function query_geoip($m4is_j45 =''){
$m4is_l91805 =geoip_detect_get_info_from_ip($m4is_j45 );
 $m4is_a9317['city']=$m4is_l91805->city;
 $m4is_a9317['country_name']=$m4is_l91805->country_name;
 $m4is_a9317['countrycode']=$m4is_l91805->country_code;
 $m4is_a9317['isp']='';
 $m4is_a9317['latitude']=$m4is_l91805->latitude;
 $m4is_a9317['longitude']=$m4is_l91805->longitude;
 $m4is_a9317['postalcode']=$m4is_l91805->postal_code;
 $m4is_a9317['region']=$m4is_l91805->region;
 $m4is_a9317['region_name']=$m4is_l91805->region_name;
 $m4is_a9317['timezone']=$m4is_l91805->timezone;
 return $m4is_a9317;
 
}private static 
function query_hostip($m4is_j45 =''){
$m4is_a9317 =[];
 $m4is_i93625 ="http://api.hostip.info/get_json.php?ip={$m4is_j45
}&position=true";
 $m4is_l91805 =json_decode(m4is_a01587::m4is_f46823($m4is_i93625));
 if(is_object($m4is_l91805)){
$m4is_a9317['country_name']=ucwords(strtolower($m4is_l91805->country_name));
 $m4is_a9317['city']=substr($m4is_l91805->city, 0, strpos($m4is_l91805->city, ','));
 $m4is_a9317['region']=substr($m4is_l91805->city, 2 + strpos($m4is_l91805->city, ', '));
 $m4is_a9317['latitude']=$m4is_l91805->lat;
 $m4is_a9317['longitude']=$m4is_l91805->lng;
 $m4is_a9317['countrycode']=$m4is_l91805->country_code;
 $m4is_a9317['isp']='';
 $m4is_a9317['postalcode']='';
 $m4is_a9317['region_name']='';
 $m4is_a9317['timezone']='';
 
}return $m4is_a9317;
 
}private static 
function query_ipstack($m4is_j45 =''){
 $m4is_a9317 =[];
 $m4is_e36 =defined('IPSTACK_API_KEY')? constant('IPSTACK_API_KEY'): false;
 if($m4is_e36){
$m4is_i93625 ="http://api.ipstack.com/{$m4is_j45
}?access_key={$m4is_e36
}&output=json";
 $m4is_l91805 =json_decode(m4is_a01587::m4is_f46823($m4is_i93625));
 $m4is_a9317['city']=$m4is_l91805->city;
 $m4is_a9317['country_name']=$m4is_l91805->country_name;
 $m4is_a9317['countrycode']=$m4is_l91805->country_code;
 $m4is_a9317['isp']='';
 $m4is_a9317['latitude']=$m4is_l91805->latitude;
 $m4is_a9317['longitude']=$m4is_l91805->longitude;
 $m4is_a9317['postalcode']=$m4is_l91805->zip;
 $m4is_a9317['region']=$m4is_l91805->region_code;
 $m4is_a9317['region_name']=$m4is_l91805->region_name;
 $m4is_a9317['timezone']='';
 
}return $m4is_a9317;
 
}private static 
function query_ipapi($m4is_j45 =''){
$m4is_a9317 =[];
 $m4is_e36 =defined('IPAPI_API_KEY')? constant('IPAPI_API_KEY'): false;
 if($m4is_e36){
$m4is_i93625 ="http://api.ipapi.com/{$m4is_j45
}?access_key={$m4is_e36
}&format=1";
 $m4is_l91805 =json_decode(m4is_a01587::m4is_f46823($m4is_i93625));
 $m4is_a9317['city']=$m4is_l91805->city;
 $m4is_a9317['country_name']=$m4is_l91805->country_name;
 $m4is_a9317['countrycode']=$m4is_l91805->country_code;
 $m4is_a9317['isp']='';
 $m4is_a9317['latitude']=$m4is_l91805->latitude;
 $m4is_a9317['longitude']=$m4is_l91805->longitude;
 $m4is_a9317['postalcode']=$m4is_l91805->zip;
 $m4is_a9317['region']=$m4is_l91805->region_code;
 $m4is_a9317['region_name']=$m4is_l91805->region_name;
 $m4is_a9317['timezone']='';
 
}return $m4is_a9317;
 
}private static 
function query_ipinfodb($m4is_j45 =''){
$m4is_a9317 =[];
 $m4is_e36 =defined('IPINFODB_API_KEY')? constant('IPINFODB_API_KEY'): false;
 if($m4is_e36){
$m4is_i93625 ="http://api.ipinfodb.com/v3/ip-city/?key={$m4is_e36
}&ip={$m4is_j45
}&format=json";
 $m4is_l91805 =json_decode(m4is_a01587::m4is_f46823($m4is_i93625));
 $m4is_a9317['city']=$m4is_l91805->cityName;
 $m4is_a9317['country_name']=$m4is_l91805->countryName;
 $m4is_a9317['countrycode']=$m4is_l91805->countryCode;
 $m4is_a9317['isp']='';
 $m4is_a9317['postalcode']=$m4is_l91805->zipCode;
 $m4is_a9317['region_name']=$m4is_l91805->regionName;
 $m4is_a9317['timezone']=$m4is_l91805->timeZone;
 $m4is_a9317['latitude']=$m4is_l91805->latitude;
 $m4is_a9317['longitude']=$m4is_l91805->longitude;
 $m4is_a9317['region']='';
 
}sleep(1);
 return $m4is_a9317;
 
}   static 
function m4is_a61(){
return ['&Aring;land Islands' =>'AX', 'Afghanistan' =>'AF', 'Aland Islands' =>'AX', 'Albania' =>'AL', 'Algeria' =>'DZ', 'American Samoa' =>'AS', 'Andorra' =>'AD', 'Angola' =>'AO', 'Anguilla' =>'AI', 'Antarctica' =>'AQ', 'Antigua and Barbuda' =>'AG', 'Argentina' =>'AR', 'Armenia' =>'AM', 'Aruba' =>'AW', 'Australia' =>'AU', 'Austria' =>'AT', 'Azerbaijan' =>'AZ', 'Bahamas (the)' =>'BS', 'Bahrain' =>'BH', 'Bangladesh' =>'BD', 'Barbados' =>'BB', 'Belarus' =>'BY', 'Belgium' =>'BE', 'Belize' =>'BZ', 'Benin' =>'BJ', 'Bermuda' =>'BM', 'Bhutan' =>'BT', 'Bolivia (Plurinational State of)' =>'BO', 'Bonaire, Sint Eustatius and Saba' =>'BQ', 'Bosnia and Herzegovina' =>'BA', 'Botswana' =>'BW', 'Bouvet Island' =>'BV', 'Brazil' =>'BR', 'British Indian Ocean Territory (the)' =>'IO', 'Brunei Darussalam' =>'BN', 'Bulgaria' =>'BG', 'Burkina Faso' =>'BF', 'Burundi' =>'BI', 'Cabo Verde' =>'CV', 'Cambodia' =>'KH', 'Cameroon' =>'CM', 'Canada' =>'CA', 'Cayman Islands (the)' =>'KY', 'Central African Republic (the)' =>'CF', 'Chad' =>'TD', 'Chile' =>'CL', 'China' =>'CN', 'Christmas Island' =>'CX', 'Cocos (Keeling) Islands (the)' =>'CC', 'Colombia' =>'CO', 'Comoros (the)' =>'KM', 'Congo (the Democratic Republic of the)' =>'CD', 'Congo (the)' =>'CG', 'Cook Islands (the)' =>'CK', 'Costa Rica' =>'CR', 'Croatia' =>'HR', 'Cuba' =>'CU', 'Cura&ccedil;ao' =>'CW', 'Cyprus' =>'CY', 'Czech Republic (the)' =>'CZ', 'Denmark' =>'DK', 'Djibouti' =>'DJ', 'Dominica' =>'DM', 'Dominican Republic (the)' =>'DO', 'Ecuador' =>'EC', 'Egypt' =>'EG', 'El Salvador' =>'SV', 'Equatorial Guinea' =>'GQ', 'Eritrea' =>'ER', 'Estonia' =>'EE', 'Ethiopia' =>'ET', 'Falkland Islands (the) [Malvinas]' =>'FK', 'Faroe Islands (the)' =>'FO', 'Fiji' =>'FJ', 'Finland' =>'FI', 'France' =>'FR', 'French Guiana' =>'GF', 'French Polynesia' =>'PF', 'French Southern Territories (the)' =>'TF', 'Gabon' =>'GA', 'Gambia (the)' =>'GM', 'Georgia' =>'GE', 'Germany' =>'DE', 'Ghana' =>'GH', 'Gibraltar' =>'GI', 'Greece' =>'GR', 'Greenland' =>'GL', 'Grenada' =>'GD', 'Guadeloupe' =>'GP', 'Guam' =>'GU', 'Guatemala' =>'GT', 'Guernsey' =>'GG', 'Guinea-Bissau' =>'GW', 'Guinea' =>'GN', 'Guyana' =>'GY', 'Haiti' =>'HT', 'Heard Island and McDonald Islands' =>'HM', 'Holy See (the)' =>'VA', 'Honduras' =>'HN', 'Hong Kong' =>'HK', 'Hungary' =>'HU', 'Iceland' =>'IS', 'India' =>'IN', 'Indonesia' =>'ID', 'Iran (Islamic Republic of)' =>'IR', 'Iraq' =>'IQ', 'Ireland' =>'IE', 'Isle of Man' =>'IM', 'Israel' =>'IL', 'Italy' =>'IT', 'Jamaica' =>'JM', 'Japan' =>'JP', 'Jersey' =>'JE', 'Johnston Island' =>'JT', 'Jordan' =>'JO', 'Kazakhstan' =>'KZ', 'Kenya' =>'KE', 'Kiribati' =>'KI', 'Korea (the Republic of)' =>'KR', 'Kuwait' =>'KW', 'Kyrgyzstan' =>'KG', 'Laos' =>'LA', 'Latvia' =>'LV', 'Lebanon' =>'LB', 'Lesotho' =>'LS', 'Liberia' =>'LR', 'Libya' =>'LY', 'Liechtenstein' =>'LI', 'Lithuania' =>'LT', 'Luxembourg' =>'LU', 'Macao' =>'MO', 'Macedonia (the former Yugoslav Republic of)' =>'MK', 'Madagascar' =>'MG', 'Malawi' =>'MW', 'Malaysia' =>'MY', 'Maldives' =>'MV', 'Mali' =>'ML', 'Malta' =>'MT', 'Marshall Islands (the)' =>'MH', 'Martinique' =>'MQ', 'Mauritania' =>'MR', 'Mauritius' =>'MU', 'Mayotte' =>'YT', 'Mexico' =>'MX', 'Micronesia (Federated States of)' =>'FM', 'Midway Islands' =>'MI', 'Moldova (the Republic of)' =>'MD', 'Monaco' =>'MC', 'Mongolia' =>'MN', 'Montenegro' =>'ME', 'Montserrat' =>'MS', 'Morocco' =>'MA', 'Mozambique' =>'MZ', 'Myanmar' =>'MM', 'Namibia' =>'NA', 'Nauru' =>'NR', 'Nepal' =>'NP', 'Netherlands (the)' =>'NL', 'Netherlands Antilles' =>'AN', 'New Caledonia' =>'NC', 'New Zealand' =>'NZ', 'Nicaragua' =>'NI', 'Niger (the)' =>'NE', 'Nigeria' =>'NG', 'Niue' =>'NU', 'Norfolk Island' =>'NF', 'Northern Mariana Islands (the)' =>'MP', 'Norway' =>'NO', 'Oman' =>'OM', 'Pakistan' =>'PK', 'Palau' =>'PW', 'Palestine, State of' =>'PS', 'Panama' =>'PA', 'Papua New Guinea' =>'PG', 'Paraguay' =>'PY', 'Peru' =>'PE', 'Philippines (the)' =>'PH', 'Pitcairn' =>'PN', 'Poland' =>'PL', 'Portugal' =>'PT', 'Puerto Rico' =>'PR', 'Qatar' =>'QA', 'R&eacute;union' =>'RE', 'Romania' =>'RO', 'Russian Federation (the)' =>'RU', 'Rwanda' =>'RW', 'Saint Barth&eacute;lemy' =>'BL', 'Saint Helena, Ascension and Tristan da Cunha' =>'SH', 'Saint Kitts and Nevis' =>'KN', 'Saint Lucia' =>'LC', 'Saint Martin (French part)' =>'MF', 'Saint Pierre and Miquelon' =>'PM', 'Saint Vincent and the Grenadines' =>'VC', 'Samoa' =>'WS', 'San Marino' =>'SM', 'Sao Tome and Principe' =>'ST', 'Saudi Arabia' =>'SA', 'Senegal' =>'SN', 'Serbia' =>'RS', 'Seychelles' =>'SC', 'Sierra Leone' =>'SL', 'Singapore' =>'SG', 'Sint Maarten (Dutch part)' =>'SX', 'Slovakia' =>'SK', 'Slovenia' =>'SI', 'Solomon Islands' =>'SB', 'Somalia' =>'SO', 'South Africa' =>'ZA', 'South Georgia and the South Sandwich Islands' =>'GS', 'South Sudan' =>'SS', 'Southern Rhodesia' =>'RH', 'Spain' =>'ES', 'Sri Lanka' =>'LK', 'St. Barthelemy' =>'BL', 'Sudan (the)' =>'SD', 'Suriname' =>'SR', 'Svalbard and Jan Mayen' =>'SJ', 'Swaziland' =>'SZ', 'Sweden' =>'SE', 'Switzerland' =>'CH', 'Syrian Arab Republic' =>'SY', 'Taiwan (Province of China)' =>'TW', 'Tajikistan' =>'TJ', 'Tanzania, United Republic of' =>'TZ', 'Thailand' =>'TH', 'Timor-Leste' =>'TL', 'Togo' =>'TG', 'Tokelau' =>'TK', 'Tonga' =>'TO', 'Trinidad and Tobago' =>'TT', 'Tunisia' =>'TN', 'Turkey' =>'TR', 'Turkmenistan' =>'TM', 'Turks and Caicos Islands (the)' =>'TC', 'Tuvalu' =>'TV', 'Uganda' =>'UG', 'Ukraine' =>'UA', 'United Arab Emirates (the)' =>'AE', 'United Kingdom' =>'UK', 'United States Minor Outlying Islands (the)' =>'UM', 'United States' =>'US', 'Upper Volta' =>'HV', 'Uruguay' =>'UY', 'Uzbekistan' =>'UZ', 'Vanuatu' =>'VU', 'Venezuela (Bolivarian Republic of)' =>'VE', 'Viet Nam' =>'VN', 'Virgin Islands (British)' =>'VG', 'Virgin Islands (U.S.)' =>'VI', 'Wallis and Futuna' =>'WF', 'Western Sahara' =>'EH', 'Yemen' =>'YE', 'Zambia' =>'ZM', 'Zimbabwe' =>'ZW', "C&ocirc;te d'Ivoire" =>'CI', "Korea (the Democratic People's Republic of)" =>'KP', "Lao People's Democratic Republic (the)" =>'LA', ];
 
}static 
function m4is_h926($m4is_m79 =''){
if(strlen($m4is_m79)== 2){
return $m4is_m79;
 
}$m4is_y27396 =strtolower($m4is_m79);
 $m4is_q86 =self::m4is_a61();
 foreach($m4is_q86 as $m4is_k52736 =>$m4is_v3458){
if($m4is_y27396 == $m4is_k52736){
return $m4is_v3458;
 
}
}return $m4is_m79;
 
} static 
function m4is_e669($m4is_v84769 =''){
if(strlen($m4is_v84769)> 2){
return $m4is_v84769;
 
}$m4is_q86 =self::m4is_a61();
 $m4is_v84769 =strtoupper($m4is_v84769);
 foreach($m4is_q86 as $m4is_k52736 =>$m4is_v3458){
if($m4is_v84769 == $m4is_v3458){
return $m4is_k52736;
 
}
}return $m4is_v84769;
 
}static 
function m4is_v46($m4is_m79 =''){
if(empty($m4is_m79)){
return;
 
}$m4is_q86 =self::m4is_a61();
 if(strlen($m4is_m79)> 2){
if(isset($m4is_q86[$m4is_m79])){
return $m4is_q86[$m4is_m79];
 
}
}else{
$m4is_m79 =strtoupper($m4is_m79);
 foreach ($m4is_q86 as $m4is_j58 =>$country_code){
if($country_code == $m4is_m79){
return $m4is_j58;
 
}
}
}return '';
 
} 
}

