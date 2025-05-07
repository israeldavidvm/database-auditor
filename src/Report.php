<?php

namespace Israeldavidvm\DatabaseAuditor;
use Exception;

class Report {


    public $verificationList;

    public function __construct(
        $verificationList=[],
        ) {

        $this->verificationList=$verificationList;

    }

    public function addVerification($element,$result,$message){
        $verification = [
            'result'=>$result,
            'message'=>$message
        ];
        $this->verificationList[$element]=$verification;
    }


}
