<?php
namespace ZendServerJobQueueWorker;

class Module extends \ZRay\ZRayModule
{

    public function config()
    {
        return array(
            'extension' => array(
                'name' => 'JobQueueWorker'
            ),
            'defaultPanels' => array(
                'jobDetail' => false,
            ),
            'panels' => array(
                'myJobDetail' => array(
                    'display' => true,
                    'logo' => 'logo.png',
                    'menuTitle' => 'Job Detail',
                    'panelTitle' => 'Job Detail',
                    'resources' => array(
                    )
                )
            )
        );
    }
}
