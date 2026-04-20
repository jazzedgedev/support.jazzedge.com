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
class m4is_d86 {
static private $m4is_l07426;
 static private $m4is_l67501;
 static private $m4is_p6895;
 private const CACHE_TTL =5 * MINUTE_IN_SECONDS;
  static 
function m4is_c961(){
global $wpdb;
 $_SERVER['HTTP_ACCEPT_LANGUAGE']=$_SERVER['HTTP_ACCEPT_LANGUAGE']?? '';
 self::$m4is_l07426 =$wpdb->prefix . 'memberium_lang';
 self::$m4is_l67501 ='memberium';
 self::$m4is_p6895 =explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
 
}   public static 
function m4is_n61(): string {
return self::$m4is_l07426;
 
}public static 
function m4is_w13(): array {
global $wpdb;
 $m4is_c0873 =$wpdb->get_charset_collate();
 $m4is_e80 =self::$m4is_l07426;
 $m4is_v2613 ="CREATE TABLE {$m4is_e80
} (\n" . "id int(11) NOT NULL AUTO_INCREMENT, \n" . "language varchar(10) NOT NULL, \n" . "context varchar(160) NOT NULL, \n" . "name varchar(40) NOT NULL, \n" . "origtext text NOT NULL, \n" . "value text NOT NULL, \n" . "KEY language (language), \n" . "KEY context (context), \n" . "KEY name (name), \n" . "KEY origtext (origtext(255) ), \n" . "PRIMARY KEY  (id) \n" . ") ENGINE=InnoDB {$m4is_c0873
};
";
 return ['table' =>$m4is_e80, 'sql' =>$m4is_v2613 ];
 
}    public static 
function m4is_e97($m4is_t560, $m4is_e63195, $m4is_g36127 ){
if($m4is_g36127 <> 'memberium' ){
return $m4is_t560;
 
}global $wpdb;
 static $m4is_e52716 =[];
 static $m4is_e85349;
 $m4is_e85349 ??= self::m4is_p6562();
 $m4is_o14 ='memberium/gettext';
 $m4is_v2613 ="SELECT `value` FROM %i WHERE `language` IN ( {$m4is_e85349
} ) AND `origtext` = %s ORDER BY id LIMIT 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_l07426, $m4is_e63195 );
 $m4is_j54781 =sha1($m4is_v2613 );
 $m4is_t265 =false;
 $m4is_v586 =wp_cache_get($m4is_j54781, $m4is_o14, false, $m4is_t265 );
 if($m4is_t265 ){
return $m4is_v586;
 
}if(array_key_exists($m4is_j54781, $m4is_e52716 )){
return $m4is_e52716[$m4is_j54781];
 
}$m4is_v586 =$wpdb->get_var($m4is_v2613 );
 if(!empty($m4is_v586 )){
$m4is_t560 =$m4is_v586;
 
}$m4is_t560 =empty($m4is_v586 )? $m4is_t560 : $m4is_v586;
 $m4is_e52716[$m4is_j54781]=$m4is_t560;
 wp_cache_set($m4is_j54781, $m4is_t560, $m4is_o14, self::CACHE_TTL );
 return $m4is_t560;
 
} public static 
function m4is_q2631(string $m4is_t560, $m4is_e63195, $m4is_s90, $m4is_g36127 ): string {
if($m4is_g36127 <> 'memberium' ){
return $m4is_t560;
 
}global $wpdb;
 static $m4is_e52716 =[];
 static $m4is_e85349;
 $m4is_o14 ='memberium/gettext/context';
 $m4is_e85349 ??= self::m4is_p6562();
 $m4is_v2613 ="SELECT `value` FROM %i WHERE `language` IN ( {$m4is_e85349
} ) AND `context` = %s AND `origtext` = %s ORDER BY `language` DESC, `id` ASC LIMIT 1";
 $m4is_v2613 =$wpdb->prepare($m4is_v2613, self::$m4is_l07426, $m4is_s90, $m4is_e63195 );
 $m4is_j54781 =sha1($m4is_v2613 );
 $m4is_t265 =false;
 $m4is_v586 =wp_cache_get($m4is_j54781, $m4is_o14, false, $m4is_t265 );
 if($m4is_t265 ){
return $m4is_v586;
 
}else{
if(array_key_exists($m4is_j54781, $m4is_e52716 )){
return $m4is_e52716[$m4is_j54781];
 
}
}$m4is_v586 =$wpdb->get_var($m4is_v2613 );
 $m4is_t560 =empty($m4is_v586 )? $m4is_t560 : $m4is_v586;
 wp_cache_set($m4is_j54781, $m4is_t560, $m4is_o14, self::CACHE_TTL );
 $m4is_e52716[$m4is_j54781]=$m4is_t560;
 return $m4is_t560;
 
} public static 
function m4is_x15(): void {
load_plugin_textdomain('memberium', FALSE, basename(__DIR__ . '/lang/' ));
 
}    public static 
function m4is_e93706(): array {
static $m4is_e85349 =[];
 if(empty($m4is_e85349 )){
$m4is_d976 =self::$m4is_p6895;
 foreach(self::$m4is_p6895 as $m4is_f619){
$m4is_f619 =explode(';q=', $m4is_f619 );
 $m4is_f619[0]=strtolower($m4is_f619[0]);
 $m4is_f619[1]=isset($m4is_f619[1])? $m4is_f619[1]: 1;
 $m4is_e85349[$m4is_f619[0]]=$m4is_f619[1];
 
}foreach($m4is_e85349 as $m4is_r93654 =>$m4is_d81702 ){
$m4is_v89370 =substr($m4is_r93654, 0, strpos($m4is_r93654, '-' ));
 if(!empty($m4is_v89370 )){
if(!isset($m4is_e85349[$m4is_v89370])){
$m4is_e85349[$m4is_v89370]=$m4is_d81702 - .01;
 
}
}
}arsort($m4is_e85349, SORT_NUMERIC );
 
}return $m4is_e85349;
 
}   private static 
function m4is_p6562(): string {
static $m4is_d579;
 if($m4is_d579 ){
return $m4is_d579;
 
}$m4is_e85349 =[];
 $m4is_e85349[]='';
 $m4is_e85349[]=trim(get_option('WPLANG', 'en_us' ));
 $m4is_u450 =self::$m4is_p6895;
 $m4is_u450 =explode(',', strtr($m4is_u450[0], '-', '_' ));
 if(!empty($m4is_u450 )){
foreach($m4is_u450 as $m4is_l9671 ){
$m4is_e85349[]=$m4is_l9671;
 if(strlen($m4is_l9671 )> 2){
$m4is_e85349[]=substr($m4is_l9671, 0, 2 );
 
}
}
}$m4is_e85349 =array_unique($m4is_e85349 );
 $m4is_d579 ="'" . implode("','", $m4is_e85349 ). "'";
 return $m4is_d579;
 
}    static 
function m4is_w635(): array {
return ['ar' =>'Arabic', 'ar-AE' =>'Arabic (United Arab Emirates)', 'ar-BH' =>'Arabic (Bahrain)', 'ar-DZ' =>'Arabic (Algeria)', 'ar-EG' =>'Arabic (Egypt)', 'ar-IQ' =>'Arabic (Iraq)', 'ar-JO' =>'Arabic (Jordan)', 'ar-KW' =>'Arabic (Kuwait)', 'ar-LB' =>'Arabic (Lebanon)', 'ar-LY' =>'Arabic (Libya)', 'ar-MA' =>'Arabic (Morocco)', 'ar-OM' =>'Arabic (Oman)', 'ar-QA' =>'Arabic (Qatar)', 'ar-SA' =>'Arabic (Saudi Arabia)', 'ar-SD' =>'Arabic (Sudan)', 'ar-SY' =>'Arabic (Syria)', 'ar-TN' =>'Arabic (Tunisia)', 'ar-YE' =>'Arabic (Yemen)', 'be' =>'Belarusian', 'be-BY' =>'Belarusian (Belarus)', 'bg' =>'Bulgarian', 'bg-BG' =>'Bulgarian (Bulgaria)', 'ca' =>'Catalan', 'ca-ES' =>'Catalan (Spain)', 'cs' =>'Czech', 'cs-CZ' =>'Czech (Czech Republic)', 'da' =>'Danish', 'da-DK' =>'Danish (Denmark)', 'de' =>'German', 'de-AT' =>'German (Austria)', 'de-CH' =>'German (Switzerland)', 'de-DE' =>'German (Germany)', 'de-GR' =>'German (Greece)', 'de-LU' =>'German (Luxembourg)', 'el' =>'Greek', 'el-CY' =>'Greek (Cyprus)', 'el-GR' =>'Greek (Greece)', 'en' =>'English', 'en-AU' =>'English (Australia)', 'en-CA' =>'English (Canada)', 'en-GB' =>'English (United Kingdom)', 'en-IE' =>'English (Ireland)', 'en-IN' =>'English (India)', 'en-MT' =>'English (Malta)', 'en-NZ' =>'English (New Zealand)', 'en-PH' =>'English (Philippines)', 'en-SG' =>'English (Singapore)', 'en-US' =>'English (United States)', 'en-ZA' =>'English (South Africa)', 'es' =>'Spanish', 'es-AR' =>'Spanish (Argentina)', 'es-BO' =>'Spanish (Bolivia)', 'es-CL' =>'Spanish (Chile)', 'es-CO' =>'Spanish (Colombia)', 'es-CR' =>'Spanish (Costa Rica)', 'es-CU' =>'Spanish (Cuba)', 'es-DO' =>'Spanish (Dominican Republic)', 'es-EC' =>'Spanish (Ecuador)', 'es-ES' =>'Spanish (Spain)', 'es-GT' =>'Spanish (Guatemala)', 'es-HN' =>'Spanish (Honduras)', 'es-MX' =>'Spanish (Mexico)', 'es-NI' =>'Spanish (Nicaragua)', 'es-PA' =>'Spanish (Panama)', 'es-PE' =>'Spanish (Peru)', 'es-PR' =>'Spanish (Puerto Rico)', 'es-PY' =>'Spanish (Paraguay)', 'es-SV' =>'Spanish (El Salvador)', 'es-US' =>'Spanish (United States)', 'es-UY' =>'Spanish (Uruguay)', 'es-VE' =>'Spanish (Venezuela)', 'et' =>'Estonian', 'et-EE' =>'Estonian (Estonia)', 'fi' =>'Finnish', 'fi-FI' =>'Finnish (Finland)', 'fr' =>'French', 'fr-BE' =>'French (Belgium)', 'fr-CA' =>'French (Canada)', 'fr-CH' =>'French (Switzerland)', 'fr-FR' =>'French (France)', 'fr-LU' =>'French (Luxembourg)', 'ga' =>'Irish', 'ga-IE' =>'Irish (Ireland)', 'he' =>'Hebrew', 'he-IL' =>'Hebrew (Israel)', 'hi' =>'Hindi', 'hi-IN' =>'Hindi (India)', 'hr' =>'Croatian', 'hr-HR' =>'Croatian (Croatia)', 'hu' =>'Hungarian', 'hu-HU' =>'Hungarian (Hungary)', 'id' =>'Indonesian', 'id-ID' =>'Indonesian (Indonesia)', 'is' =>'Icelandic', 'is-IS' =>'Icelandic (Iceland)', 'it' =>'Italian', 'it-CH' =>'Italian (Switzerland)', 'it-IT' =>'Italian (Italy)', 'ja' =>'Japanese', 'ja-JP' =>'Japanese (Japan)', 'ko' =>'Korean', 'ko-KR' =>'Korean (South Korea)', 'lt' =>'Lithuanian', 'lt-LT' =>'Lithuanian (Lithuania)', 'lv' =>'Latvian', 'lv-LV' =>'Latvian (Latvia)', 'mk' =>'Macedonian', 'mk-MK' =>'Macedonian (Macedonia)', 'ms' =>'Malay', 'ms-MY' =>'Malay (Malaysia)', 'mt' =>'Maltese', 'mt-MT' =>'Maltese (Malta)', 'nl' =>'Dutch', 'nl-BE' =>'Dutch (Belgium)', 'nl-NL' =>'Dutch (Netherlands)', 'nn-NO' =>'Norwegian (Norway, Nynorsk)', 'no' =>'Norwegian', 'no-NO' =>'Norwegian (Norway)', 'pl' =>'Polish', 'pl-PL' =>'Polish (Poland)', 'pt' =>'Portuguese', 'pt-BR' =>'Portuguese (Brazil)', 'pt-PT' =>'Portuguese (Portugal)', 'ro' =>'Romanian', 'ro-RO' =>'Romanian (Romania)', 'ru' =>'Russian', 'ru-RU' =>'Russian (Russia)', 'sk' =>'Slovak', 'sk-SK' =>'Slovak (Slovakia)', 'sl' =>'Slovenian', 'sl-SI' =>'Slovenian (Slovenia)', 'sq' =>'Albanian', 'sq-AL' =>'Albanian (Albania)', 'sr' =>'Serbian', 'sr-BA' =>'Serbian (Bosnia and Herzegovina)', 'sr-CS' =>'Serbian (Serbia and Montenegro)', 'sr-Latn' =>'Serbian (Latin)', 'sr-Latn-BA' =>'Serbian (Latin, Bosnia and Herzegovina)', 'sr-Latn-ME' =>'Serbian (Latin, Montenegro)', 'sr-Latn-RS' =>'Serbian (Latin, Serbia)', 'sr-ME' =>'Serbian (Montenegro)', 'sr-RS' =>'Serbian (Serbia)', 'sv' =>'Swedish', 'sv-SE' =>'Swedish (Sweden)', 'th' =>'Thai', 'th-TH' =>'Thai (Thailand)', 'tr' =>'Turkish', 'tr-TR' =>'Turkish (Turkey)', 'uk' =>'Ukrainian', 'uk-UA' =>'Ukrainian (Ukraine)', 'vi' =>'Vietnamese', 'vi-VN' =>'Vietnamese (Vietnam)', 'zh' =>'Chinese', 'zh-CN' =>'Chinese (China)', 'zh-HK' =>'Chinese (Hong Kong)', 'zh-SG' =>'Chinese (Singapore)', 'zh-TW' =>'Chinese (Taiwan)', ];
 
} 
}

