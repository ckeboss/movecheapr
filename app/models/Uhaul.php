<?php

use Jyggen\Curl\Curl;
use Jyggen\Curl\Request;
use Jyggen\Curl\Response;

class Uhaul extends Model {
    
    private $tokens = false;
    
    public function __construct() {
        if(!$this->tokens) {
            $this->tokens = $this->_getTokens();
        }
    }
    
    public function getQuote(DateTime $date, $zipcode_from, $zipcode_to) {
        $this->_setDateAndLocation($date, $zipcode_from, $zipcode_to);
        
        $request = new Request('http://www.uhaul.com/reservations/RatesTrucks.aspx');
        
        $request->setOption(CURLOPT_FOLLOWLOCATION, true);
        $request->setOption(CURLOPT_COOKIEFILE, APP_PATH.'storage/cookies/uhaul_cookie.txt');
        $request->setOption(CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36');
        
        $request->execute();
        if ($request->isSuccessful()) {
            $resp_text = $request->getResponse()->getContent();
            
            preg_match_all('/equipTitle">(.*?)<\/h2>/', $resp_text, $title_matches);
            preg_match_all('/ctl00_ContentPlaceHolder1_rptTrucks_ctl0._lblDisplayRate">(.*?)<\/span>/', $resp_text, $price_matches);
            
            $title_matches[1] = array_slice($title_matches[1], 0, count($price_matches[1]));
            
            $truck_prices = [];
            
            foreach($title_matches[1] as $key=>$value) {
                $fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
                $curr = 'USD';
                $truck_prices[] = ['truck_length' => (int) $title_matches[1][$key], 'price' => $fmt->parseCurrency($price_matches[1][$key], $curr)];
            }
            
            return $truck_prices;
        }
        throw new Exception($resquest->getErrorMessage());
    }

    private function _setDateAndLocation(DateTime &$date, &$zipcode_from, &$zipcode_to) {
        $request = new Request('http://www.uhaul.com/');
        
        $request->setOption(CURLOPT_POST, true);
        $request->setOption(CURLOPT_COOKIEFILE, APP_PATH.'storage/cookies/uhaul_cookie.txt');
        $request->setOption(CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36');
        
        $request->setOption(CURLOPT_POSTFIELDS, [
            '__EVENTTARGET'      => '', 
            '__EVENTARGUMENT'    => '', 
            '__VIEWSTATE'        => $this->tokens['viewstate'], 
            '__EVENTVALIDATION'  => $this->tokens['eventvalidation'],
            'js'                 => '',
            'ctl00$SearchBox1$SearchText' => 'Search',
            'ctl00$SearchBox1$hiddenSuggested' => '',
            'ctl00$ContentPlaceHolder1$ctrlSharedEquipmentSearch1$dpFrenchMoveDateDisplay' => '',
            'ctl00$ContentPlaceHolder1$ctrlSharedEquipmentSearch1$dpMoveDate' => $date->format('m/d/Y'),
            'ctl00$ContentPlaceHolder1$ctrlSharedEquipmentSearch1$tbPickupLocation' => $zipcode_from,
            'ctl00$ContentPlaceHolder1$ctrlSharedEquipmentSearch1$tbDropOffLocation' => $zipcode_to,
            'ctl00$ContentPlaceHolder1$ctrlSharedEquipmentSearch1$btnSubmit' => 'Get rates'
        ]);
        
        $request->execute();
        if ($request->isSuccessful()) {
            $request->close();
            return true;
        }
        throw new Exception($resquest->getErrorMessage());
    }
    
    private function _getTokens() {
        
        $request = new Request('http://www.uhaul.com/');
        
        if(!file_exists(APP_PATH.'storage/cookies/uhaul_cookie.txt')) {
            touch(APP_PATH.'storage/cookies/uhaul_cookie.txt');
        }
        
        $f = @fopen(APP_PATH.'storage/cookies/uhaul_cookie.txt', "r+");
        if ($f !== false) {
            ftruncate($f, 0);
            fclose($f);
        }
        
        $request->setOption(CURLOPT_FOLLOWLOCATION, true);
        $request->setOption(CURLOPT_COOKIESESSION, true );
        $request->setOption(CURLOPT_COOKIEJAR, APP_PATH.'storage/cookies/uhaul_cookie.txt' );
        // $request->setOption(CURLOPT_COOKIEFILE, APP_PATH.'storage/cookies/uhaul_cookie.txt' );
        $request->setOption(CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36');
        $request->execute();
        
        if ($request->isSuccessful()) {
            
            preg_match('/<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*?)".*?<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*?)"/s', $request->getRawResponse(), $matches);
            
            $request->close();
            if(!empty($matches[1]) && !empty($matches[2])) {
                return ['viewstate' => $matches[1], 'eventvalidation' => $matches[2]];
            }
            throw new Exception('Unable to parse tokens');
        }
        throw new Exception($resquest->getErrorMessage());
    }
}