<?php


namespace Coachsome\BaseReview\Repositories;

use Coachsome\BaseReview\Models\BaseReview;
use Prettus\Repository\Eloquent\BaseRepository;

class BaseReviewRepository extends BaseRepository
{

    public function model()
    {
        return BaseReview::class;
    }
}
