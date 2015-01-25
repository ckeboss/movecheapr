<?php

Class HomeController extends BaseController
{

    public function index()
    {
        // $uhaul  = new Uhaul();
        // $budget = new Budget();
        // $quote['uhaul'] = $uhaul->getQuote(new DateTime('2015-01-31'), '92618', '90210');
        // $quote['budget'] = $budget->getQuote(new DateTime('2015-01-31'), '92618', '90210');
        
        $this->data['title'] = 'Get the best price on a moving truck | Movecheapr';
        App::render('index.twig', $this->data);
    }
}