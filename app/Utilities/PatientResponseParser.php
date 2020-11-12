<?php

namespace App\Utilities;
use DateTime;
use Illuminate\Support\Facades\DB;
use App\Models\MedicationStatement;

class PatientResponseParser {
    private $response;

    public function __construct($response)
    {
        $this->response = json_decode($response);
    }

    public function filterFhirEntries()
    {
        $count = 0;
        if(empty($this->response)) {
            abort(500, "Response from FHIR API is empty");
        }
        $fhirObj = $this->response;
        $entries = $fhirObj->entry;

        if (count($entries)) {
            /** we grab the resource type of the first entry because we only need the type */
            $mostRecentCreatedDate = $this->getMostRecentOpiSafeRecordCreatedDateFromResourceType($this->getFhirResourceType($entries[0]));
            foreach ($entries as $entry) {
                if ($this->shouldStoreEntry($entry, $mostRecentCreatedDate)) {
                    $this->storeEntryInOpiSafe($entry);
                    ++$count;
                }
            }

            return response()->json([
                'msg' => $count ? $count . " new records stored" : "No new records stored"
            ]);

        }

        return response()->json([
            'msg' => "No new records stored"
        ]);

    }


    public function shouldStoreEntry($entry, DateTime $mostRecentCreatedDate): bool
    {
        // maybe check id too?
        $fhirID = $entry->resource->id;
        $dbRecordExists = DB::table($entry->resource->resourceType)->where('fhir_resource_id', $fhirID)->exists();
        if($dbRecordExists) {
            return false;
        }

        return $this->getFhirEntryCreateDate($entry) > $mostRecentCreatedDate;
    }

    public function getMostRecentOpiSafeRecordCreatedDateFromResourceType($fhirResourceType): DateTime
    {
        try {
            $lastRecord = DB::table($fhirResourceType)
                ->latest()
                ->first();
        } catch (Exception $e) {
            return (new \DateTime("1920-10-10 00:00:00"));
        }

        return $lastRecord ? (new \DateTime($lastRecord->created_at)) : (new \DateTime("1920-10-10 00:00:00"));
    }

    public function getFhirEntryCreateDate($entry)
    {
        $className = 'App\Models\\'.$entry->resource->resourceType;
        $model = new $className();
        /**
         * getCreateDateFromEntry() is unique to each ResourceType/Model
         */
        return (new DateTime($model->getCreateDateFromEntry($entry)));
    }

    public function storeEntryInOpiSafe($entry)
    {
        try {
            DB::table($entry->resource->resourceType)->insert([
                'fhir_resource_id' => $this->getFhirResourceId($entry),
                'fhir_body' => json_encode($entry),
                'fhir_resource_type' => $this->getFhirResourceType($entry),
            ]);
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    public function getFhirResourceId($entry) {
        return $entry->resource->id;
    }

    public function getFhirResourceType($entry) {
        return $entry->resource->resourceType;
    }


}








