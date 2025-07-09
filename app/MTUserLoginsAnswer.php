<?php
//+------------------------------------------------------------------+
//|                                             MetaTrader 5 Web API |
//|                   Copyright 2001-2015, MetaQuotes Software Corp. |
//|                                        http://www.metaquotes.net |
//+------------------------------------------------------------------+
/**
 * Class work with users
 */
namespace App;

 
class MTUserLoginsAnswer
  {
  public $RetCode = '-1';
  public $ConfigJson = '';

  /**
   * From json get array logins
   * @return array(int)
   */
  public function GetFromJson()
    {
 
    $objects = MTJson::Decode($this->ConfigJson);
    if($objects == null) return null;
    $result = array();
    //---
    foreach($objects as $obj)
    {
      //---
      $result[] = (int)$obj;
    }
    //---
    $objects = null;
    //---
    return $result;
    }
  }

 

?>