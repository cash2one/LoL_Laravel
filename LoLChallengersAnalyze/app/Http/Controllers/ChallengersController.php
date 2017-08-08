<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Elasticsearch\ClientBuilder;

class ChallengersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($champion_name)
    {
        $client = ClientBuilder::create()->build();
        $result_arr = [];

      $champion_result = $client->search([
        "index" => "lol_champions",
        "type" => "champion",
        "size" => 200,
        "body" => [
          "query" => [
            "match" => [
                "key" => $champion_name
            ]
          ]
        ]
      ]);

      $champion_id = $champion_result["hits"]["hits"][0]["_source"]["id"];
      $champion_key = $champion_result["hits"]["hits"][0]["_source"]["key"];
      $champion_name = $champion_result["hits"]["hits"][0]["_source"]["name"];

      // print($champion_name);
      // var_dump($champion_result["hits"]["hits"][0]["_source"]);

      $match_results = $client->search([
            "index" => "timelines",
            "type" => "timeline",
            "size" => 0,
            "body" => [
                "query" => [
                    "match" => [
                        "championId" => $champion_id
                    ]
                ],
                "aggs" => [
                    "games" => [
                        "terms" => [
                            "field" => "gameId"
                        ],
                        "aggs" => [
                            "items" => [
                                "terms" => [
                                    "field" => "itemId",
                                    "order" => [
                                        "min_buying_time" => "asc"
                                    ]
                                ],
                                "aggs" => [
                                    "min_buying_time" => [
                                        "min" => [
                                            "field" => "timestamp"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

      var_dump($match_results["aggregations"]["games"]["buckets"][0]["key"]);

      // var_dump($match_results["aggregations"]["games"]["buckets"]);

      $games = $match_results["aggregations"]["games"]["buckets"];
      $item_build_history = [];

      foreach($games as $game) {
        $items = $game["items"]["buckets"];

        foreach($items as $item){
            print($item["key"]);
            print(" ");
            print($item["min_buying_time"]["value"]);
            print("<br>");

            $item_build_history = $item_build_history +
                                    array(strval($item["key"]) => $item["min_buying_time"]["value"]);
        }
      }

      var_dump($item_build_history);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
      $client = ClientBuilder::create()->build();
      $result_arr = [];

      $results = $client->search([
        "index" => "lol_champions",
        "type" => "champion",
        "size" => 200,
        "body" => [
          "query" => [
            "match_all" => new \stdClass()
          ]
        ]
      ]);

      $champions = $results['hits']['hits'];

      foreach($champions as $champion) {
        $champion_info = $champion["_source"];

        $result_arr[] = array("id" => $champion_info["id"],
                                "key" => $champion_info["key"],
                                "name" => $champion_info["name"]);
      }

      $this->sortArrayByKey($result_arr, "key");

      return view('home', ['champions' => $result_arr]);
    }

    private function sortArrayByKey( &$array, $sortKey, $sortType = SORT_ASC) {
        $tmpArray = array();
        foreach ( $array as $key => $row ) {
            $tmpArray[$key] = $row[$sortKey];
        }
        array_multisort( $tmpArray, $sortType, $array );
        unset( $tmpArray );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
