<?php


namespace App\Services;


use App\Entities\SearchValue;
use App\Entities\User;
use Carbon\Carbon;

class SearchValueService
{
    public function createCategory(User $user, $incomingValues)
    {
        if ($user && $incomingValues) {
            $existedSearchValue = SearchValue::where('user_id', $user->id)
                ->where('date', '=', Carbon::now()->format('Y-m-d'))
                ->first();
            if ($existedSearchValue) {
                $oldValues = json_decode($existedSearchValue->value);
                foreach ($incomingValues as $newValue) {
                    $notExist = true;
                    foreach ($oldValues as $oldValue) {
                        if ($oldValue == $newValue) {
                            $notExist = false;
                            break;
                        }
                    }
                    if($notExist){
                        $oldValues[] = $newValue;
                    }
                }
                $existedSearchValue->type = 'category';
                $existedSearchValue->value = json_encode($oldValues);
                $existedSearchValue->save();
            } else {
                $searchValue = new SearchValue();
                $searchValue->type = 'category';
                $searchValue->user_id = $user->id;
                $searchValue->value = json_encode($incomingValues);
                $searchValue->date_time = Carbon::now();
                $searchValue->date = Carbon::now()->format('Y-m-d');
                $searchValue->save();
            }
        }
    }
}
