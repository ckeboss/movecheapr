<?php

Class HomeController extends BaseController
{

    public function index()
    {
        $this->loadCss('bootstrap-datetimepicker.min.css');
        
        $this->meta('og:image', 'http://movecheapr.com/images/fb_share.png');
        $this->loadJs('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js', ['location' => 'external']);
        $this->loadJs('bootstrap-datetimepicker.min.js');
        $this->loadJs('main.js');
        
        $this->data['title'] = 'Compare Moving Truck Prices | Movecheapr';
        App::render('index.twig', $this->data);
    }
    
    public function learn_more() {
        $this->data['title'] = 'What\'s the Deal? | Movecheapr';
        App::render('learn-more.twig', $this->data);
    }
    
    public function search() {
        $user    = null;
        $message = '';
        $success = false;

        try{
            $input = Input::post();
            
            $quote   = new stdClass;
            $trucks  = [];
            $size_classes = [];
            
            $uhaul  = new Uhaul();
            $budget = new Budget();
            $quote->uhaul  = $uhaul->getQuote (new DateTime($input['date']), $input['zipcode_from'], $input['zipcode_to']);
            $quote->budget = $budget->getQuote(new DateTime($input['date']), $input['zipcode_from'], $input['zipcode_to']);
            
            
            foreach($quote->uhaul as $truck) {
                if(!in_array($truck['truck_length'], $size_classes)) {
                    $size_classes[] = $truck['truck_length'];
                }
            }
            
            foreach($quote->budget as $truck) {
                if(!in_array($truck['truck_length'], $size_classes)) {
                    $size_classes[] = $truck['truck_length'];
                }
            }
            
            asort($size_classes);
            
            foreach($size_classes as $size) {
                
                foreach($quote->uhaul as $truck) {
                    if($truck['truck_length'] === $size) {
                        $trucks[$size]['uhaul'] = $truck['price'];
                    }
                }
                
                foreach($quote->budget as $truck) {
                    if($truck['truck_length'] === $size) {
                        $trucks[$size]['budget'] = $truck['price'];
                    }
                }
            }
            
            $success = true;
            $message = 'Successfully retrieved rates';
        }catch (Exception $e){
            $message = $e->getMessage();
        }

        
        Response::headers()->set('Content-Type', 'application/json');
        Response::setBody(json_encode(
            array(
                'success'   => $success,
                'data'      => $trucks,
                'message'   => $message,
                'code'      => $success ? 200 : 500
            )
        ));
    }
}