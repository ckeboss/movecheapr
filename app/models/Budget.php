<?php

use Jyggen\Curl\Curl;
use Jyggen\Curl\Request;
use Jyggen\Curl\Response;

class Budget extends Model {
    
    private $tokens = false;
    
    public function __construct() {
        if(!$this->tokens) {
            $this->tokens = $this->_getTokens();
        }
    }
    
    public function getQuote(DateTime $date, $zipcode_from, $zipcode_to) {
        $this->_setDateAndLocation($date, $zipcode_from, $zipcode_to);
        
        $request = new Request('https://www.budgettruck.com/DesktopModules/BudgetTruck.WebAPI/API/Truck/GetTrucks?pickupDate=&dropOffDate=');
        
        $request->setOption(CURLOPT_COOKIEFILE, APP_PATH.'storage/cookies/budget_cookie.txt');
        $request->setOption(CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36');
        
        $request->execute();
        if ($request->isSuccessful()) {
            $resp_object = json_decode($request->getResponse()->getContent());
            
            $truck_prices = [];
            
            foreach($resp_object->trucksSorted as $truck_object) {
                $truck_prices[] = ['type' => $truck_object->Name, 'price' => $truck_object->TruckRate];
            }
                           
            return $truck_prices;
        }
        throw new Exception($resquest->getErrorMessage());
    }

    private function _setDateAndLocation(DateTime &$date, &$zipcode_from, &$zipcode_to) {
        
        $request = new Request('https://www.budgettruck.com/DesktopModules/BudgetTruck.WebAPI/API/Home/AddCookie?pickDate='.$date->format('m/d/Y').'&dropDate='.$date->format('m/d/Y').'&pickUpLoc='.$zipcode_from.'&dropOffLoc='.$zipcode_to.'&Coupon=');
        
        $request->setOption(CURLOPT_COOKIEJAR, APP_PATH.'storage/cookies/budget_cookie.txt' );
        $request->setOption(CURLOPT_COOKIEFILE, APP_PATH.'storage/cookies/budget_cookie.txt');
        $request->setOption(CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36');
        $request->execute();
        // echo $request->getRawResponse();
        // exit;
        $request = new Request('https://www.budgettruck.com/DesktopModules/BudgetTruck.WebAPI/API/Home/SearchReservation');
        
        $request->setOption(CURLOPT_CUSTOMREQUEST, "POST");
        $request->setOption(CURLOPT_COOKIEJAR, APP_PATH.'storage/cookies/budget_cookie.txt' );
        $request->setOption(CURLOPT_COOKIEFILE, APP_PATH.'storage/cookies/budget_cookie.txt');
        $request->setOption(CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36');
        
        // $request->setOption(CURLOPT_BINARYTRANSFER, true);
        
        $data_string = json_encode([
                // 'PickUpLocation'     => $zipcode_from,
                // 'DropOffLocation'    => $zipcode_to,
                // 'PickupDate'         => $date->format('m/d/Y'),
                // 'DueInDate'          => $date->format('m/d/Y'),
                // 'IsLocal'            => false,
               "DefaultVehicleClassCode" => null,
               "DropOffLocation" => $zipcode_to,
               "IsAutoPromotion" => false,
               "IsLocal" => false,
               "IsPartnerURL" => false,
               "IsReservationEdit" => false,
               "LocationDetails" => null,
               "PickUpLocation" => $zipcode_from,
               "RentalLocationDefaultDealer" => 0,
               "TruckDetails" => null,
               "AccountNumber" => null,
               "AccountTypeCode" => null,
               "ApplicationType" => "P",
               "AppliedPromotion" => null,
               "CUS_GPS_IND" => null,
               "CancelFlag" => false,
               "CancelReason" => null,
               "CancelReasonOther" => null,
               "CancelRequested" => false,
               "ChargedAmt" => 0,
               "CouponID1" => "",
               "CouponID2" => null,
               "DestinationDealerNumber" => 0,
               "DispatchDealerNumber" => 0,
               "DueInDate" => $date->format('m/d/Y'),
               "DueInTime" => null,
               "IsNew" => false,
               "PaymentData" => null,
               "PickupDate" => $date->format('m/d/Y'),
               "PickupTime" => null,
               "PrimaryCustomer" => null,
               "ReferenceNumber" => null,
               "RentalAmt" => 0,
               "RentalDays" => 0,
               "RentalMiles" => 0,
               "RentalNeed" => null,
               "RentalTransactionStageType" => null,
               "RentalTransactionType" => null,
               "TaxAmt" => 0,
               "TaxExemptData" => null,
               "TaxSummary" => null,
               "TransactionDate" => null,
               "TransactionTime" => null,
               "VehicleClassCode" => null,
               "VehicleMade" => null
            ]);
        
        $request->setOption(CURLOPT_POSTFIELDS, $data_string);
        
        $request->setOption(CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        
        $request->execute();
        if ($request->isSuccessful()) {
            return true;
        }
        throw new Exception($resquest->getErrorMessage());
    }
    
    private function _getTokens() {
        
        $request = new Request('https://www.budgettruck.com/');
        
        if(!file_exists(APP_PATH.'storage/cookies/budget_cookie.txt')) {
            touch(APP_PATH.'storage/cookies/budget_cookie.txt');
        }
        
        $request->setOption(CURLOPT_FOLLOWLOCATION, true);
        $request->setOption(CURLOPT_COOKIESESSION, true );
        $request->setOption(CURLOPT_COOKIEJAR, APP_PATH.'storage/cookies/budget_cookie.txt' );
        // $request->setOption(CURLOPT_COOKIEFILE, APP_PATH.'storage/cookies/uhaul_cookie.txt' );
        $request->setOption(CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36');
        $request->execute();
        
        if ($request->isSuccessful()) {
            return true;
        }
        throw new Exception($resquest->getErrorMessage());
    }
}