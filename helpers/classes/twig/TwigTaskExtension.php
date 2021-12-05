<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 18.11.2017
 * Time: 12:22
 */
namespace helpers\classes\twig;

use helpers\classes\enums\TaskStatusEnum;

class TwigTaskExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('tabTaskColor', [ $this, 'tabTaskColor' ]),
            new \Twig_SimpleFunction('taskStatus', [ $this, 'taskStatus' ])
        ];
    }

    public function tabTaskColor(TaskStatusEnum $taskStatus, $active = false)
    {
        switch ($taskStatus->value)
        {
            case TaskStatusEnum::InQueue:
            case TaskStatusEnum::Compiling:
            case TaskStatusEnum::InProgress:
                return $active ? "#ffff00" : "#aaaa00";

            case TaskStatusEnum::CompilingError:
            case TaskStatusEnum::ResponseError:
            case TaskStatusEnum::RuntimeError:
            case TaskStatusEnum::OverMemory:
            case TaskStatusEnum::OverTime:
            case TaskStatusEnum::InvalidOutputStream:
                return $active ? "#ff0000" : "#aa0000";

            case TaskStatusEnum::Succeed:
                return $active ? "#00ff00" : "#00aa00";
        }

        return $active ? "#c6c6c6" : "#606060";
    }

    public function taskStatus($var)
    {
        return constant("helpers\\classes\\enums\\TaskStatusEnum::{$var}");
    }
}
