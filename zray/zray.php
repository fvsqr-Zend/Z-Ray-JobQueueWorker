<?php

namespace ZendServerJobQueue;

$zre = new \ZRayExtension('JobQueueWorker');
$zre->setMetadata(array(
    'logo' => __DIR__ . DIRECTORY_SEPARATOR . 'logo.png',
    'actionsBaseUrl' => $_SERVER['REQUEST_URI'] 
));

function shutdown() {}

if (extension_loaded('Zend Job Queue')) {
 
    $q = new \ZendJobQueue();
    
    if ($q->getCurrentJobId()) {
        
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'JobQueueWorker.php';
    
        $jq = new JobQueueWorker();
        $zre->setEnabledAfter('ZendJobQueue::ZendJobQueue');
        
        register_shutdown_function('ZendServerJobQueue\shutdown');
        
        $zre->traceFunction(
            'ZendJobQueue::setCurrentJobStatus',
            function() {},
            array(
                $jq,
                'workerStatus'
            )
        );
        
        $zre->traceFunction(
            'ZendServerJobQueue\shutdown',
            function() {},
            array(
                $jq,
                'shutdown'
            )
        );
    }
}


