<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Actions;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
/**
 * Description of RestClientAction
 *
 * @author msilva
 */
class RestClientAction extends WorkflowAction {
    
    private $client;
    
    private $url = "https://api.cloudflare.com/client/v4"
            . "/zones/c20556cfa06917e51cd9ca706bb9a6e2"
            . "/dns_records?name=qa.arkhotech.space";
            
            //{{URL}}/zones/{{zone_id}}/dns_records?name=qa.arkhotech.space
    
    public function __construct($params) {
        parent::__construct($params);
        $config = json_decode($this->config,true);
        $this->headers = $config['properties']['headers'];
        $this->url = $config['properties']['url'];
        $this->method = $config['properties']['method'];
        $this->client = new Client();
    }
    
    public function configParamenters() {
         return [ "method" => [ "type" => "list", "values" =>  
                        [ "POST","GET", "DELETE" , "PUT" , "PATCH" ]],
                  "url"  => ["type" => "text" ],
                  "headers" => ["type" => "key-value"]
            ];
    }

    public function execute( $request) {
        Log::debug($this->config);
        Log::info("Realizando call");
        
       
        Log::debug("Ejecutando llamado");
       $response = $this->call($request);
       return $response->getBody()->getContents();
    }
    
    private function call($request){
        $response = null;
        //Log::debug(".".$this->headers.".");
        $verb = strtolower($this->method);
        
        
        $headers = array("X-Auth-Email" => "arkho@arkhotech.com",
            "X-Auth-Key" => "23ea8d0b31049cbf99ec445720a13fb0a1645",
            "Content-Type" => "Application/json");
        
        switch(strtoupper($this->method)){
            case "GET":
                $response = $this->client->{$verb}(
                        $this->url,
                        array("headers" => $this->headers));
                break;
            case "POST":
            case "PUT":
            case "DELETE":
                $response = $this->client->{$verb}(
                        $this->url,
                        array("headers" => $this->headers),$request);
                break;
        }
        return $response;
    }

//put your code here
}
