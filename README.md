<h1>Overview</h1>

<b>Note: this code is intended to be moved to the opsafe-workers repo<b>. 
- This application assumes we will be hitting the Cerner sandbox to retrieve any
patient records

- The idea is to make a call to the Fhir-based cerner sandbox, retrieve records based on a patient Id,
then to locally store any new records from fhir that we haven't already stored

- This api only exposes one endpoint and the format is as follows:<br>
"local.opisafe.com/api/fhir/{FhirResourceType}/{PatientId}"  

- the resource type and the patient id will be parsed from that uri to construct an actual fhir api request like so: <br>
"{fhir-api-url}/{resourceType}?patient={patientId}"

- From there we will parse each response based on the resource type and store any new records based on their "created date".
I put "created date" in quotes because the date field's name that indicates the created date may vary
