<?php
namespace ZendServerJobQueue;

class JobQueueWorker
{
    private $workerStatus = false;
    private $shutdownCalled = false;
    
    private static function getConstantText($value, $prefix)
    {
        $class = new \ReflectionClass('ZendJobQueue');
        $constants = array_filter($class->getConstants(), function($key) use ($prefix) {
            return strpos($key, $prefix) === 0;
        }, ARRAY_FILTER_USE_KEY);
        
        $constants = array_flip($constants);
    
        return $constants[$value];
    }
    
    public function workerStatus($context, &$storage)
    {
        $status = $context['functionArgs'][0];
        $status = ($status == \ZendJobQueue::OK) ? 'OK' : 'FAILED';
        $storage['workerStatus'][] = array('Status' => $status);
        $this->workerStatus = true;
    }
    
    public function shutdown($context, &$storage)
    {
        if ($this->shutdownCalled) return;
        
        $this->shutdownCalled = true;
        
        $queue = new \ZendJobQueue();
        
        $jobId = $queue->getCurrentJobId();
        $jobInfo = $queue->getJobInfo($jobId);
        
        $storage['jobInfo']['ID'] = array($jobId);
        $storage['jobInfo']['Name'] = array($jobInfo['name']);
        $storage['jobInfo']['URL'] = array( $jobInfo['script']);
        $storage['jobInfo']['Vars'] = array( $jobInfo['vars']);
        if ($jobInfo['predecessor']) $storage['jobInfo']['Predecessor'] = array( $jobInfo['predecessor']);
        if ($jobInfo['output']) $storage['jobInfo']['Output'] = array( $jobInfo['output']);
        if ($jobInfo['error']) $storage['jobInfo']['Error'] = array( $jobInfo['error']);
        
        $jobInfo['Queue'] = array('Id' => $jobInfo['queue_id'], 'Name' => $jobInfo['queue_name']);
        $jobInfo['priority'] = array($this->getConstantText($jobInfo['priority'], 'PRIORITY'));
        
        unset($jobInfo['id']);
        unset($jobInfo['status']);
        unset($jobInfo['name']);
        unset($jobInfo['script']);
        unset($jobInfo['queue_name']);
        unset($jobInfo['queue_id']);
        unset($jobInfo['predecessor']);
        unset($jobInfo['output']);
        unset($jobInfo['error']);
        unset($jobInfo['vars']);
        
        $storage['jobInfo']['Detail'] = $jobInfo;
        
        if ($this->workerStatus) return;
        
        $storage['workerStatus'][] = array('Status' => 'Status has not been set in job! Please consider using ZendJobQueue::setCurrentJobStatus() in your worker');
    }
}
