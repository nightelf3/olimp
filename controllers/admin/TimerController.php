<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 05.01.2018
 * Time: 14:43
 */
namespace controllers\admin;

use helpers\SettingsHelper;
use helpers\TemplateHelper;
use Klein\App;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;

class TimerController extends BaseAdminController
{
    public function index(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $this->header['css'][] = 'timer.css';
        $this->header['js'][] = 'timer.js';
        $this->data['timer'] = TemplateHelper::render('components/timer', [
            'olimpStart' => date("Y-m-d H:i:s", SettingsHelper::param('olimp_start', 0)),
            'olimpContinuity' => SettingsHelper::param('olimp_duration', 0)
        ]);
        if (SettingsHelper::isOlimpInProgress(false)) {
            $date = new \DateTime();
            $date->setTimestamp((int)SettingsHelper::param('olimp_start', 0));

            $this->data['time'] = [
                'year' => $date->format('Y'),
                'month' => $date->format('m'),
                'day' => $date->format('d'),
                'hours' => $date->format('H'),
                'minutes' => $date->format('i'),
                'seconds' => $date->format('s')
            ];

            $duration = (int)SettingsHelper::param('olimp_duration', 0);
            $this->data['duration']['hours'] = sprintf('%02.0f', $duration / 3600);
            $duration %= 3600;
            $this->data['duration']['minutes'] = sprintf('%02.0f', $duration / 60);
            $this->data['duration']['seconds'] = sprintf('%02.0f', $duration % 60);
        }

        return $this->render('timer');
    }

    public function save(Request $request, Response $response, ServiceProvider $service, App $app)
    {
        $this->data['time'] = [
            'year' => $request->param('t_year', 0),
            'month' => $request->param('t_month', 0),
            'day' => $request->param('t_day', 0),
            'hours' => $request->param('t_hours', 0),
            'minutes' => $request->param('t_minutes', 0),
            'seconds' => $request->param('t_seconds', 0)
        ];
        $this->data['duration'] = [
            'hours' => $request->param('d_hours', '03'),
            'minutes' => $request->param('d_minutes', '00'),
            'seconds' => $request->param('d_seconds', '00')
        ];

        $date = new \DateTime("{$this->data['time']['year']}-{$this->data['time']['month']}-{$this->data['time']['day']} {$this->data['time']['hours']}:{$this->data['time']['minutes']}:{$this->data['time']['seconds']}");
        SettingsHelper::setParam('olimp_start', $date->getTimestamp());

        $duration = (int)$this->data['duration']['hours'] * 3600;
        $duration += (int)$this->data['duration']['minutes'] * 60;
        $duration += (int)$this->data['duration']['seconds'];
        SettingsHelper::setParam('olimp_duration', $duration);

        //TODO: check saving
        return $this->index($request, $response, $service, $app);
    }
}
