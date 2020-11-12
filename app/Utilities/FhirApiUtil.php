<?php


namespace App\Utilities;


use Illuminate\Http\Request;

class FhirApiUtil
{


    const VERSION_STU2 = "stu2";

    const VERSION_R4 = "r4";

    const FHIR_API_URL = [
        self::VERSION_STU2 => self::FHIR_API_URL_STU2,
        self::VERSION_R4 => self::FHIR_API_URL_R4,
    ];

    const FHIR_API_URL_STU2 = "https://fhir-ehr.sandboxcerner.com/dstu2/0b8a0111-e8e6-4c26-a91c-5069cbc6b1ca";

    const FHIR_API_URL_R4 = "https://fhir-ehr-code.cerner.com/r4/ec2458f2-1e24-41c8-b71b-0e701af7583d";

    public static function authenticateWithFhir() {

        $client = new \GuzzleHttp\Client();

        $response = $client->post("https://fhir.com", [ // will have to diff between R4 and STU
            'headers' => [
                'Authorization' => 'Bearer {$auth_token}'
            ]
        ]);

        return $response->getBody(); // gotta figure this out
    }

    public static function getFullFhirApiEndpointFromRequest(Request $request, $version) {
        $uri = $request->path();
        $endpoint = explode("fhir", $uri)[1];
        return self::FHIR_API_URL[$version].$endpoint;
    }
}
