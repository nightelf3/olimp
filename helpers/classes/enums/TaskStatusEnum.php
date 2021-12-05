<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 01.12.2017
 * Time: 12:20
 */
namespace helpers\classes\enums;

/**
 * 0 - тільки закинута
 * 1 - в черзі
 * 2 - компілюється
 * 3 - помилка компіляції (месседж!)
 * 4 - скомпільовано/виконується
 * 5 - невірна відповідь
 * 6 - помилка виконання
 * 7 - перевищений ліміт пам'яті
 * 8 - перевищений ліміт часу
 * 9 - успіх
 * 10 - не правильний потік виводу
 **/
class TaskStatusEnum extends BaseEnum
{
    const __default = self::NoAction;

    const NoAction = 0;
    const InQueue = 1;
    const Compiling = 2;
    const CompilingError = 3;
    const InProgress = 4;
    const ResponseError = 5;
    const RuntimeError = 6;
    const OverMemory = 7;
    const OverTime = 8;
    const Succeed = 9;
    const InvalidOutputStream = 10;
}
