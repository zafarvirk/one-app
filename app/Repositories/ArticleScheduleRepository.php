<?php
/*
 * File name: ArticleScheduleRepository.php
 * Last modified: 2021.01.31 at 14:03:57
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Repositories;

use App\Models\ArticleSchedule;
use Carbon\Carbon;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class ArticleScheduleRepository
 * @package App\Repositories
 * @version January 19, 2021, 2:04 pm UTC
 *
 * @method Category findWithoutFail($id, $columns = ['*'])
 * @method Category find($id, $columns = ['*'])
 * @method Category first($columns = ['*'])
 */
class ArticleScheduleRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'article_id',
        'start_date',
        'start_time',
        'duration',
        'repeat',
        'days',
        'end_date',
        'end_time',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return ArticleSchedule::class;
    }

    public function create($dataArray = []) {
        if ($dataArray['repeat'] == 'weekly' && count($dataArray['days'])) {
            $scheduleEndDate = Carbon::createFromFormat('Y-m-d', $dataArray['end_date']);
            $dataArray['recurrence_rules'] = $this->createRecurrenceRules($dataArray['days'], $scheduleEndDate);
            $dataArray['days'] = json_encode($dataArray['days']);
        }

        return parent::create($dataArray);
    }

    public function update($dataArray = [], $id) {
        if ($dataArray['repeat'] == 'weekly' && count($dataArray['days'])) {
            $scheduleEndDate = Carbon::createFromFormat('Y-m-d', $dataArray['end_date']);
            $dataArray['recurrence_rules'] = $this->createRecurrenceRules($dataArray['days'], $scheduleEndDate);
            $dataArray['days'] = json_encode($dataArray['days']);
        }

        return parent::update($dataArray, $id);
    }

    public function calculateEndTime($start_time, $duration)
    {
        $duration = (new Carbon($duration));
        $start_time = (new Carbon($start_time));
        $end_time = $start_time->copy()->addHours($duration->hour)->addMinutes($duration->minute);
        return $end_time;
    }

    public function createRecurrenceRules($days, Carbon $scheduleEndDate)
    {
        $date = $scheduleEndDate->format('Ymd');
        $date .= "T". $scheduleEndDate->format('His'). "Z";

        $byDay = "";
        if ($days['Sunday']) {
            $byDay .= "SU,";
        }
        if ($days['Monday']) {
            $byDay .= "MO,";
        }
        if ($days['Tuesday']) {
            $byDay .= "TU,";
        }
        if ($days['Wednesday']) {
            $byDay .= "WE,";
        }
        if ($days['Thursday']) {
            $byDay .= "TH,";
        }
        if ($days['Friday']) {
            $byDay .= "FR,";
        }
        if ($days['Saturday']) {
            $byDay .= "SA,";
        }

        $byDay = rtrim($byDay, ',');

        $rules = "FREQ=WEEKLY;BYDAY=$byDay;INTERVAL=1;UNTIL=$date";
        return $rules;
    }
}
