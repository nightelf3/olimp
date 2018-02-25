<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 01.12.2017
 * Time: 13:39
 */
namespace models;
use helpers\classes\enums\TaskStatusEnum;
use helpers\UserHelper;

/**
 * Class TaskModel
 * @property int task_id
 * @property string task
 * @property string input
 * @property string output
 * @property int tests_count
 * @property double time_limit
 * @property double memory_limit
 * @property int max_score
 * @property int mulct
 * @property string name
 * @property int sort_order
 */
class TaskModel extends BaseModel
{
    protected $table = 'tasks';
    protected $primaryKey = 'task_id';
    protected $fillable = [ 'task', 'input', 'output', 'tests_count', 'time_limit', 'memory_limit', 'max_score', 'mulct', 'name', 'sort_order', 'user_id' ];
    public $timestamps = true;

    /**
     * Get task status
     * @return TaskStatusEnum
     */
    public function getStatus()
    {
        /** @var QueueModel $item */
        $item = QueueModel::select([ 'stan' ])->where([
            'user_id' => UserHelper::getUser()->user_id,
            'task_id' => $this->task_id
        ])->orderBy('created_at', 'desc')->first();

        return new TaskStatusEnum($item ? explode(',', $item->stan)[0] : TaskStatusEnum::NoAction);
    }
}
