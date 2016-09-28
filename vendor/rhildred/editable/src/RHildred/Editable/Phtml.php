<?php

namespace RHildred\Editable;

use \stdClass;

class Phtml
{
    public static $Viewbag;
    public static function render($sScript){
        $Viewbag = Phtml::$Viewbag = new stdClass();
        $Viewbag->bInLayout = false;
        $Viewbag->sScript = $sScript;
        ob_start();
        include $sScript . ".phtml";
        $sFName = $sScript . "." . date("Y") . ".html";
        if(isset($Viewbag->sOut))
           file_put_contents($sFName, $Viewbag->sOut);
        else
            file_put_contents($sFName, ob_get_contents());
        ob_end_clean();
        include $sFName;

    }
    public static function layout($sLayout){
        $Viewbag = Phtml::$Viewbag;
        if(!$Viewbag->bInLayout){
            $Viewbag->bInLayout = true;
            //don't want to output anything from sScript the first time
            if(isset($Viewbag->sScript)){
                $Viewbag->sPage = str_replace('.phtml','',basename($Viewbag->sScript));
                include_once dirname($Viewbag->sScript) . '/' . $sLayout;
                $Viewbag->sOut = ob_get_contents();
            }else{
                $Viewbag->sPage = str_replace('.phtml','',basename($_SERVER['PHP_SELF']));
                include_once $sLayout;
                exit;
            }
        }
    }
    function renderBody(){
        $Viewbag = Phtml::$Viewbag;
        if(isset($Viewbag->sScript)){
            include($Viewbag->sScript . ".phtml");
        }else{
            if(file_exists($Viewbag->sPage)){
                include($Viewbag->sPage);
            }else{
                include($Viewbag->sPage . ".phtml");
            }

        }
    }

    function renderPartial($oCollection, $sPartial)
    {
        $Viewbag = Phtml::$Viewbag;
        if(!is_array($oCollection)){
            // Get cURL resource
            $curl = curl_init();
            // Set some options - we are passing in a useragent too here
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $oCollection,
                CURLOPT_USERAGENT => 'Codular Sample cURL Request'
            ));
            // Send the request & save response to $resp
            $resp = curl_exec($curl);
            // Close request to clear up some resources
            curl_close($curl);


            $oCollection = json_decode($resp);
        }
        foreach($oCollection as $oModel){
            $Viewbag->model = $oModel;
            include $sPartial;
        }
    }

    function showcopyright(){
        $nYear = date("Y");
        if($nYear != 2014){
            echo "2014-";
        }
        echo $nYear;
    }
}
