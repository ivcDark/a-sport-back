<?php

namespace App\Service;

use App\Dto\CountryDto;
use App\Models\ModelIntegration;
use Illuminate\Support\Facades\DB;

class CountryService
{
    private string $typeIntegration;
    private string $nameModel;

    public function __construct($typeIntegration)
    {
        $this->typeIntegration = $typeIntegration;
        $this->nameModel = 'country';
    }

    public function create()
    {

    }

    public function updateOrCreate(CountryDto $countryDto): ?\App\Models\Country
    {
        $country = null;

        try {
            DB::beginTransaction();

            $country = \App\Models\Country::updateOrCreate(
                [
                    'name' => $countryDto->name,
                    'code' => $countryDto->code,
                ],
                [
                    'name' => $countryDto->name,
                    'code' => $countryDto->code,
                    'logo' => $countryDto->logo,
                ]
            );

            ModelIntegration::firstOrCreate(
                [
                    'model_id' => $country->id,
                    'model' => $this->nameModel,
                    'type_integration' => $this->typeIntegration,
                    'integration_id' => $countryDto->apiId
                ],
                [
                    'model_id' => $country->id,
                    'model' => $this->nameModel,
                    'type_integration' => $this->typeIntegration,
                    'integration_id' => $countryDto->apiId
                ]
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception->getMessage());
        }

        return $country;

    }
}
