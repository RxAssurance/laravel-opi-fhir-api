<?php

use App\Utilities\FhirApiUtil;
use App\Utilities\PatientResponseParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/store', function (Request $request) {
    return "yo!";
});


Route::get('/fhir/{resource}', function (Request $request, $resource) {

    $auth_token = FhirApiUtil::authenticateWithFhir();

    if(!$auth_token) abort(403, "Could not authenticate with fhir. please check credentials");


    if(!empty($patient = $request->get('patient'))) {

        $client = new \GuzzleHttp\Client();

        $version = ""; // need to decide version somehow. TBD

        $response = $client->get(FhirApiUtil::getFullFhirApiEndpointFromRequest($request, $version), [
            'query' => [
                'patient' => $patient
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $auth_token
            ]
        ]);

        $patientResponseParser = new PatientResponseParser($response->getBody());

        $patientResponseParser->filterFhirEntries();

    } else {
        abort(400, "Patient parameter is missing ");
    }

});
