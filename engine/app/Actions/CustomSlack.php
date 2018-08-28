<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Actions;

use Illuminate\Notifications\Messages\SlackMessage;
/**
 * Description of CustomSlack
 *
 * @author msilva
 */
class CustomSlack {
    //put your code here
    public function toSlack($notificable){
        return new SlackMessage();
    }
}
