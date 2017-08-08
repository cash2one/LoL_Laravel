<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/champions', 'ChallengersController@index');


Route::get('/champions/list', 'ChallengersController@show');
Route::get('/champions/{champion_name}', 'ChallengersController@index');


Route::get('/es', function() {
//  var_dump(new Elasticsearch\ClientBuilder);
  $client = Elasticsearch\ClientBuilder::create()->build();
//  var_dump($client);

/*
  $results = $client->search([
    "index" => "lol_champions",
    "type" => "champion",
    "body" => [
      "query" => [
        "match" => [
          "key" => "LeeSin"
        ]
      ]
    ]
  ]);
*/
  $results = $client->search([
    "index" => "lol_champions",
    "type" => "champion",
    "body" => [
      "query" => [
        "match_all" => new \stdClass()
      ]
    ]
  ]);

  $champions = $results['hits']['hits'][0]['_source'];

  var_dump($champions);
});