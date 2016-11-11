<?php

namespace ZendServerJobQueueWorker;

$zre = new \ZRayExtension('JobQueueWorker');
$zre->setMetadata(array(
    'logo' => __DIR__ . DIRECTORY_SEPARATOR . 'logo.png',
    'actionsBaseUrl' => $_SERVER['REQUEST_URI'] 
));

function shutdown() {
}

if (extension_loaded('Zend Job Queue')) {
    $q = new \ZendJobQueue();
    if ($q->getCurrentJobId()) {
        
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'JobQueueWorker.php';
    
        $jq = new JobQueueWorker();
        #$zre->setEnabledAfter('ZendJobQueue::ZendJobQueue');
        $zre->setEnabledAfter('ZendServerJobQueueWorker\shutdown');
        
        register_shutdown_function('ZendServerJobQueueWorker\shutdown');
        $zre->traceFunction(
            'ZendJobQueue::setCurrentJobStatus',
            function() {},
            array(
                $jq,
                'workerStatus'
            )
        );
        $zre->traceFunction(
            'ZendServerJobQueueWorker\shutdown',
            function() {},
            array(
                $jq,
                'shutdown'
            )
        );
    }
}


