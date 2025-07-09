<?php
namespace App\Http\Controllers\admin;
namespace App; 
/**
 * group permissions flags
 */
class MTEnPermissionsFlags
  {
  const PERMISSION_NONE = 0; // default
  const PERMISSION_CERT_CONFIRM = 1; // certificate confirmation neccessary
  const PERMISSION_ENABLE_CONNECTION = 2; // clients connections allowed
  const PERMISSION_RESET_PASSWORD = 4; // reset password after first logon
  const PERMISSION_FORCED_OTP_USAGE = 8;  // forced usage OTP
  const PERMISSION_RISK_WARNING = 16; // show risk warning window on start
  const PERMISSION_REGULATION_PROTECT = 32; // country-specific regulatory protection
  //--- enumeration borders
  const PERMISSION_ALL = 63;
  }
  class MTEnAuthMode
  {
  const AUTH_STANDARD   = 0; // standard authorization
  const AUTH_RSA1024    = 1; // RSA1024 certificate
  const AUTH_RSA2048    = 2; // RSA2048 certificate
  const AUTH_RSA_CUSTOM = 3; // RSA custom
  //--- enumeration borders
  const AUTH_FIRST = MTEnAuthMode::AUTH_STANDARD;
  const AUTH_LAST  = MTEnAuthMode::AUTH_RSA_CUSTOM;
  }
  class MTEnReportsMode
  {
  const REPORTS_DISABLED = 0; // reports disabled
  const REPORTS_STANDARD = 1; // standard mode
  //--- enumeration borders
  const REPORTS_FIRST = MTEnReportsMode::REPORTS_DISABLED;
  const REPORTS_LAST  = MTEnReportsMode::REPORTS_STANDARD;
  }
  /**
 * reports generation flags
 */
class MTEnReportsFlags
{
const REPORTSFLAGS_NONE    = 0; // none
const REPORTSFLAGS_EMAIL   = 1; // send reports through email
const REPORTSFLAGS_SUPPORT = 2; // send reports copies on support email
//--- enumeration borders
const REPORTSFLAGS_ALL = 3;
}

/**
* news modes
*/
class MTEnNewsMode
{
const NEWS_MODE_DISABLED = 0; // disable news
const NEWS_MODE_HEADERS  = 1; // enable only news headers
const NEWS_MODE_FULL     = 2; // enable full news
//--- enumeration borders
const NEWS_MODE_FIRST = MTEnNewsMode::NEWS_MODE_DISABLED;
const NEWS_MODE_LAST  = MTEnNewsMode::NEWS_MODE_FULL;
}

/**
* internal email modes
*/
class MTEnMailMode
{
const MAIL_MODE_DISABLED = 0; // disable internal email
const MAIL_MODE_FULL     = 1; // enable internal email
//--- enumeration borders
const MAIL_MODE_FIRST = MTEnMailMode::MAIL_MODE_DISABLED;
const MAIL_MODE_LAST  = MTEnMailMode::MAIL_MODE_FULL;
}

/**
* client history limits
*/
class MTEnHistoryLimit
{
const TRADE_HISTORY_ALL      = 0; // unlimited
const TRADE_HISTORY_MONTHS_1 = 1; // one month
const TRADE_HISTORY_MONTHS_3 = 2; // 3 months
const TRADE_HISTORY_MONTHS_6 = 3; // 6 months
const TRADE_HISTORY_YEAR_1   = 4; // 1 year
const TRADE_HISTORY_YEAR_2   = 5; // 2 years
const TRADE_HISTORY_YEAR_3   = 6; // 3 years
//--- enumeration borders
const TRADE_HISTORY_FIRST = MTEnHistoryLimit::TRADE_HISTORY_ALL;
const TRADE_HISTORY_LAST  = MTEnHistoryLimit::TRADE_HISTORY_YEAR_3;
}

/**
* free margin calculation modes
*/
class MTEnFreeMarginMode
{
const FREE_MARGIN_NOT_USE_PL = 0; // don't use floating profit and loss
const FREE_MARGIN_USE_PL     = 1; // use floating profit and loss
const FREE_MARGIN_PROFIT     = 2; // use floating profit only
const FREE_MARGIN_LOSS       = 3; // use floating loss only
//--- enumeration borders
const FREE_MARGIN_FIRST = MTEnFreeMarginMode::FREE_MARGIN_NOT_USE_PL;
const FREE_MARGIN_LAST  = MTEnFreeMarginMode::FREE_MARGIN_LOSS;
}

/**
* EnTransferMode
*/
class MTEnTransferMode
{
const TRANSFER_MODE_DISABLED   = 0;
const TRANSFER_MODE_NAME       = 1;
const TRANSFER_MODE_GROUP      = 2;
const TRANSFER_MODE_NAME_GROUP = 3;
//--- enumeration borders
const TRANSFER_MODE_FIRST = MTEnTransferMode::TRANSFER_MODE_DISABLED;
const TRANSFER_MODE_LAST  = MTEnTransferMode::TRANSFER_MODE_NAME_GROUP;
}

/**
* stop-out mode
*/
class MTEnStopOutMode
{
const STOPOUT_PERCENT = 0; // stop-out in percent
const STOPOUT_MONEY   = 1; // stop-out in money
//--- enumeration borders
const STOPOUT_FIRST = MTEnStopOutMode::STOPOUT_PERCENT;
const STOPOUT_LAST  = MTEnStopOutMode::STOPOUT_MONEY;
}

/**
* Mode of calculation of the free margin of the fixed income
*/
class MTEnMarginFreeProfitMode
{
const FREE_MARGIN_PROFIT_PL   = 0; // both fixed loss and profit on free margin
const FREE_MARGIN_PROFIT_LOSS = 1; // only fixed loss on free margin
//--- enumeration borders
const FREE_MARGIN_PROFIT_FIRST = MTEnMarginFreeProfitMode::FREE_MARGIN_PROFIT_PL;
const FREE_MARGIN_PROFIT_LAST  = MTEnMarginFreeProfitMode::FREE_MARGIN_PROFIT_LOSS;
}

/**
* group risk management mode
*/
class MTEnMarginMode
{
const MARGIN_MODE_RETAIL            = 0;  // Retail FX, Retail CFD, Retail Futures
const MARGIN_MODE_EXCHANGE_DISCOUNT = 1;  // Exchange, margin discount rates based
const MARGIN_MODE_RETAIL_HEDGED     = 2;  // Retail FX, Retail CFD, Retail Futures with hedged positions
//--- enumeration borders
const MARGIN_MODE_FIRST = MTEnMarginMode::MARGIN_MODE_RETAIL;
const MARGIN_MODE_LAST  = MTEnMarginMode::MARGIN_MODE_RETAIL_HEDGED;
}

/**
* margin calculation flags
*/
class MTEnGroupMarginFlags
{
const MARGIN_FLAGS_NONE      = 0; // none
const MARGIN_FLAGS_CLEAR_ACC = 1; // clear accumulated profit at end of day
//--- enumeration borders
const MARGIN_FLAGS_ALL = MTEnGroupMarginFlags::MARGIN_FLAGS_CLEAR_ACC;
}

/**
* trade rights flags
*/
class MTEnGroupTradeFlags
{
const TRADEFLAGS_NONE            = 0;   // none
const TRADEFLAGS_SWAPS           = 1;   // allow swaps charges
const TRADEFLAGS_TRAILING        = 2;   // allow trailing stops
const TRADEFLAGS_EXPERTS         = 4;   // allow expert advisors
const TRADEFLAGS_EXPIRATION      = 8;   // allow orders expiration
const TRADEFLAGS_SIGNALS_ALL     = 16;  // allow trade signals
const TRADEFLAGS_SIGNALS_OWN     = 32;  // allow trade signals only from own server
const TRADEFLAGS_SO_COMPENSATION = 64;  // allow negative balance compensation after stop out
//--- enumeration borders
const TRADEFLAGS_DEFAULT = 31;
const TRADEFLAGS_ALL     = 127;
}


?>