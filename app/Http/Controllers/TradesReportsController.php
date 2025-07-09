<?php

namespace App\Http\Controllers;

use App\Models\GroupCopier;
use App\Models\Master;
use App\Models\MetaServer;
use App\Models\MtUserList;
use App\MTDealProtocol;
use App\MTEnPositionAction;
use App\MTPositionProtocol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TradesReportsController extends Controller
{

    public function __construct()
    {
        try {
            $this->mt5connection();
            $this->uid = auth('sanctum')->user() ? auth('sanctum')->user()->id : null;
           }
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => 'Something went wrong',
            ], 400);
        }
    }

    public function getMt5Positions($mt5id, $page)
    {
        try {


            $page_offset = (int)$page;
            $getPosition = new MTPositionProtocol($this->cn); // Initialize MT connection

            // Get total positions for the user
            $totalPositions = 0;
            $getPositionTotal = $getPosition->PositionGetTotal($mt5id, $totalPositions);
            $page_count = (int)ceil($totalPositions / 50); // Assume 50 positions per page

            $positions = [];

            // Validate the page offset
            if (($page > $page_count && $page_offset > 1) || $totalPositions === 0) {
                return response()->json([
                    'position_count' => $totalPositions,
                    'page_count' => $page_count,
                    'current_page' => $page,
                    'positions' => $positions,
                    'message' => 'Not enough records to show',
                ], 200);
            }

            // Calculate the offset for pagination
            if ($page > 0) {
                $offset = ($page - 1) * 50;
            } else {
                return response()->json([
                    'message' => 'Something went wrong',
                    'error' => 'Invalid Page Number'
                ], 400);
            }

            // Fetch positions using pagination
            $getPagePositions = $getPosition->PositionGetPage($mt5id, $offset, 50, $positions);

            // Prepare positions data for response
            $userPositions = [];
            $i = 0;

            foreach ($getPagePositions as $position) {
                $userPositions[$i]['login'] = $mt5id;
                $userPositions[$i]['position'] = $position->Position;
                $userPositions[$i]['symbol'] = $position->Symbol;
                $userPositions[$i]['price_open'] = $position->PriceOpen;
                $userPositions[$i]['price_current'] = $position->PriceCurrent;
                $userPositions[$i]['volume'] = $position->Volume;
                $userPositions[$i]['profit'] = $position->Profit;
                $userPositions[$i]['time_create'] = date("Y-m-d H:i:s", $position->TimeCreate);
                $userPositions[$i]['time_update'] = date("Y-m-d H:i:s", $position->TimeUpdate);
                $userPositions[$i]['comment'] = $position->Comment;
                switch ($position->Action) {
                    case MTEnPositionAction::POSITION_BUY:
                        $action_var = "Buy";
                        break;
                    case MTEnPositionAction::POSITION_SELL:
                        $action_var = "Sell";
                        break;
                    default:
                        $action_var = "Unknown";
                        break;
                }
                $userPositions[$i]['action'] = $action_var;
                $i++;
            }

            return response()->json([
                'position_count' => $totalPositions,
                'page_count' => $page_count,
                'current_page' => $page,
                'positions' => $userPositions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 400);
        }
    }



    public function getMt5Deals($mt5id, $page)
    {

        try {

            $from = 'January 1, 2020';
            $to = date("F d, Y");
            $page_offset = (int)$page;
            $getdeal2 = new MTDealProtocol($this->cn);                                                                   //initialize mt connection
            $getdealtotal = $getdeal2->DealGetTotal($mt5id, $from, $to, $total);                                   //fetch total deals from MT5
            $page_count = $getdealtotal / 50;                                                                              //fixed page data size as 50 records
            $page_count = (int)ceil($page_count);                                                                        //total page count
            $userdeal = array();

            // returns error on empty deal state or if request has a higher page offset number than available
            if (($page > $page_count && $page_offset > 1) || $getdealtotal == 0) {
                return response()->json([
                    'deal_count' => $getdealtotal,
                    'page_count' => $page_count,
                    'current_page' => $page,
                    'deals' => $userdeal,
                    'message' => 'Not enough records to show',

                ], 200);
            }

            // calculates page offset for mt connection
            else if ($page > 0) {
                $offset = ($page - 1) * 50;
            }

            // error when page number is passed as 0
            else {
                return response()->json([
                    'message' => 'Something went wrong',
                    'error' => 'Invalid Page Number'
                ], 400);
            }
            //dd($offset);

            $getpagedeal = $getdeal2->DealGetPage($mt5id, $from, $to, $offset, 50, $getdeal2);                      // fetch deals from MT5
            $i = 0;


            foreach ($getpagedeal as $deal) {
                $userdeal[$i]['login'] = $mt5id;
                $userdeal[$i]['time'] = date("Y-m-d H:i:s", $deal->Time);
                $userdeal[$i]['order'] = $deal->Deal;
                $userdeal[$i]['symbol'] = $deal->Symbol;
                $userdeal[$i]['price'] = $deal->Price;
                $userdeal[$i]['profit'] = $deal->Profit;
                $userdeal[$i]['ex_position'] = $deal->ExpertPositionID;
                $userdeal[$i]['comment'] = $deal->Comment;
                switch ($deal->Action) {
                    case 0:
                        $action_var = "Buy";
                        break;
                    case 1:
                        $action_var = "Sell";
                        break;
                    case 2:
                        $action_var = "Balance";
                        break;
                    case 3:
                        $action_var = "Credit";
                        break;
                    case 4:
                        $action_var = "Charge";
                        break;
                    case 5:
                        $action_var = "Correction";
                        break;
                    case 6:
                        $action_var = "Bonus";
                        break;
                    case 7:
                        $action_var = "Commission";
                        break;
                    case 8:
                        $action_var = "Commission Daily";
                        break;
                    case 9:
                        $action_var = "Commission Monthly";
                        break;
                    case 10:
                        $action_var = "Agent Daily";
                        break;
                    case 11:
                        $action_var = "Agnet Monthly";
                        break;
                    case 12:
                        $action_var = "Interestrate";
                        break;
                    case 13:
                        $action_var = "Buy Canceled";
                        break;
                    case 14:
                        $action_var = "Sell Canceled";
                        break;
                    case 15:
                        $action_var = "Dividend";
                        break;
                    case 16:
                        $action_var = "Dividend Franked";
                        break;
                    case 17:
                        $action_var = "Tax";
                        break;
                    case 18:
                        $action_var = "Agent";
                        break;
                    case 19:
                        $action_var = "Compensation";
                        break;
                }
                $userdeal[$i]['action'] = $action_var;
                $i++;
            }

            return response()->json([
                'deal_count' => $getdealtotal,
                'page_count' => $page_count,
                'current_page' => $page,
                'deals' => $userdeal,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => 'Something went wrong',
            ], 400);
        }
    }
    public function getMastersWithOpenPositions()
    {
        try {

            $masters = Master::orderBy('created_at','desc')->limit(10)
            ->get();


            $allPositions = [];

            foreach ($masters as $master) {
                $mt5id = $master->mc_mt5_id;


                $positions = $this->fetchOpenPositions($mt5id);

                if (!empty($positions)) {
                    $allPositions = array_merge($allPositions, $positions);
                }
            }
            //  // ✅ Sort positions by `time_create` in descending order (latest first)
            // usort($allPositions, function ($a, $b) {
            //     return strtotime($b['time_create']) - strtotime($a['time_create']);
            // });

            // // ✅ Get only the latest 10 positions
            // $latestPositions = array_slice($allPositions, 0, 10);

            return response()->json([
                'success' => true,
                'masters' => $masters,
                'open_positions' => $allPositions,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    private function fetchOpenPositions($mt5id)
    {
        try {
            $getPosition = new MTPositionProtocol($this->cn);

            $totalPositions = 0;
            $getPosition->PositionGetTotal($mt5id, $totalPositions);

            $positions = [];
            $getPagePositions = $getPosition->PositionGetPage($mt5id, 0, $totalPositions, $positions);

            $userPositions = [];

            foreach ($getPagePositions as $position) {
                $userPositions[] = [
                    'login' => $mt5id,
                    'position' => $position->Position,
                    'symbol' => $position->Symbol,
                    'price_open' => $position->PriceOpen,
                    'price_current' => $position->PriceCurrent,
                    'volume' => $position->Volume,
                    'profit' => $position->Profit,
                    'time_create' => date("Y-m-d H:i:s", $position->TimeCreate),
                    'time_update' => date("Y-m-d H:i:s", $position->TimeUpdate),
                    'comment' => $position->Comment,
                    'action' => ($position->Action == MTEnPositionAction::POSITION_BUY) ? "Buy" : "Sell",
                ];
            }

            return $userPositions;
        } catch (\Exception $e) {
            return [];
        }
    }
    public function generateTradeStatistics()
    {
        try {

            $masterTradesExecuted = rand(1000, 1200);

            $tradesPassedToSlaves = rand(1500, 2500);

            $successfulSlaveTrades = rand((int) ($tradesPassedToSlaves * 0.85), (int) ($tradesPassedToSlaves * 0.95));

            $successPercentage = round(($successfulSlaveTrades / $tradesPassedToSlaves) * 100, 2);

            return response()->json([
                'success' => true,
                'data' => [
                    'master_trades_executed' => $masterTradesExecuted,
                    'trades_passed_to_slaves' => $tradesPassedToSlaves,
                    'successful_slave_trades' => $successfulSlaveTrades,
                    'success_percentage' => $successPercentage
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 400);
        }
    }


}
