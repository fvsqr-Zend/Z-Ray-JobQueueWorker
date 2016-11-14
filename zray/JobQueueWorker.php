<?php
namespace ZendServerJobQueueWorker;

class JobQueueWorker
{
    private $workerStatus = false;
    private $shutdownCalled = false;
    
    private $jobValueToFilter = ['id', 'status', 'name','script', 'queue_name', 'queue_id', 'predecessor', 'output', 'error','vars'];
    
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
        
        $storage['jobInfo'] = $this->processJobInfo($jobId, $jobInfo);
        $storage['jobDetail'][] = $this->processJobDetail($jobId, $jobInfo);
        
        if ($this->workerStatus) return;
        $storage['workerStatus'][] = array('Status' => 'Status has not been set in job! Please consider using ZendJobQueue::setCurrentJobStatus() in your worker');
    }
    
    private function processJobInfo($jobId, $jobInfo) {
        $info = [];
        $info['ID'] = array($jobId);
        $info['Name'] = array($jobInfo['name']);
        $info['URL'] = array( $jobInfo['script']);
        $info['Vars'] = array( $jobInfo['vars']);
        if ($jobInfo['predecessor']) $info['Predecessor'] = array( $jobInfo['predecessor']);
        if ($jobInfo['output']) $info['Output'] = array( $jobInfo['output']);
        if ($jobInfo['error']) $info['Error'] = array( $jobInfo['error']);
        
        $jobInfo['Queue'] = array('Id' => $jobInfo['queue_id'], 'Name' => $jobInfo['queue_name']);
        $jobInfo['priority'] = $this->getConstantText($jobInfo['priority'], 'PRIORITY');
        
        foreach ($this->jobValueToFilter as $key) {
            unset ($jobInfo[$key]);
        }
        
        $info['Detail'] = $jobInfo;
        
        return $info;
    }
    
    private function processJobDetail($jobId, $jobInfo) {
        $detail = [
            'id' => $jobId,
            'url' => $jobInfo['script']
        ];
        
        return $detail;
    }
}
