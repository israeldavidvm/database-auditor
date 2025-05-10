<?php

namespace Israeldavidvm\DatabaseAuditor;
use Exception;

class Report {

    public $databaseAuditor;
    public $verificationList;

    public function __construct(
        $databaseAuditor,
        $verificationList=[],
        ) {
        $this->databaseAuditor=$databaseAuditor;
        $this->verificationList=$verificationList;

    }

    public function addVerification($element,$result,$message){
        $verification = [
            'result'=>$result,
            'message'=>$message
        ];
        $this->verificationList[$element]=$verification;
    }

    public function numGoodStateElements(){

        $count=0;

        foreach ($this->verificationList as $key => $verification) {
            if($this->isGoodResult($verification['result'])){
                $count+=1;

                // echo "$key tiene un buen resultado".$verification['result'];
            }else {
                // echo "$key tiene un mal resultado ".$verification['result'];

            }

        } 


        return $count;

    }

    public function isGoodResult($result){


        foreach ($this->databaseAuditor->validationAlgorithms as $algorithm) {
            
            if($algorithm::isGoodResult($result)){
                return true;
            }
        }

        return false;

    }



    public function numScanElements(){

        return count($this->verificationList);

    }

    public function reportToString(): string
    {

        $content=$this->reportResumeToString();

        foreach ($this->databaseAuditor->validationAlgorithms as $validationAlgorithmName => $validationAlgorithm) {
            $content .= $validationAlgorithmName . " : " . $validationAlgorithm->explainAlgorithm() . "\n";
        }

        foreach ($this->verificationList as $key => $verification) {
            $content .= $verification['message'] . "\n";
        }

        return $content;

    }

    public function reportResumeToString(){
        $content="Elementos totales(ET): ".$this->numScanElements().PHP_EOL;
        $content.="Elementos en buen estado(EG): ".$this->numGoodStateElements().PHP_EOL;

        $content = "Elementos Revisados : Resultados Algoritmos aplicados" . PHP_EOL;
        $content .= "------------------------------------------------" . PHP_EOL;

        foreach ($this->verificationList as $key => $verification) {
        
            if($this->isGoodResult($verification['result'])){
                $content .= $key . " : " . $verification['result'] . PHP_EOL;
            }else{
                $content .= $key . " : " . $verification['result'] .' <------REVISAR'. PHP_EOL;

            }
        
        }

        $content .= PHP_EOL . 'Significado de los resultados:' . PHP_EOL . PHP_EOL;

        foreach ($this->databaseAuditor->validationAlgorithms as $validationAlgorithmName => $validationAlgorithm) {
            $content .= $validationAlgorithm::explainPossibleResults();
        }

        return $content;
    }


}
