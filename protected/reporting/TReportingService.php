<?php
class TReportingService extends TService{
	private $_reports=array();

	/**
	 * Initializes this module.
	 * This method is required by the IModule interface.
	 * @param TXmlElement configuration for this module, can be null
	 */
	public function init($config){
		foreach($config->getElementsByTagName('report') as $report){
			if(($id=$report->getAttribute('id'))!==null)
				$this->_reports[$id]=$report;
			else
				throw new TConfigurationException('reportservice_id_required');
		}
	}

	/**
	 * @return string the requested report path
	 */
	protected function determineRequestedReportPath(){
		return $this->getRequest()->getServiceParameter();
	}

	/**
	 * Runs the service.
	 * This method is invoked by application automatically.
	 */
	public function run(){
		$id=$this->getRequest()->getServiceParameter();
		if(isset($this->_reports[$id]))
		{
			$reportConfig=$this->_reports[$id];
			$properties=$reportConfig->getAttributes();
			if(($class=$properties->remove('class'))!==null){
				$report=Prado::createComponent($class);
				if($report instanceof TReportDocument){
					foreach($properties as $name=>$value)
						$report->setSubproperty($name,$value);
					$report->init($reportConfig);

					if (isset($this->Request['startdate']) &&isset($this->Request['enddate']) ){
						$file = $report->getReportFile($this->Request['startdate'],$this->Request['enddate']);
					}else{
						throw new TConfigurationException('reportservice_timespan_missing');
					}
					$this->getResponse()->appendHeader("Content-Type: application/x-msexcel; name=\"$file\"\r\n");
					$this->getResponse()->appendHeader("Content-Transfer-Encoding: base64\r\n");
					$this->getResponse()->appendHeader("Content-Disposition: attachment; filename=\"$file\"\r\n\r\n");


					$fh=fopen($file, "rb");
					while(!feof($fh)) {
						$buf = fread($fh, 4096);
						echo $buf;

					}
					fclose($fh);
					$this->getResponse()->appendHeader("Connection: close");
				}
				else
					throw new TConfigurationException('reportservice_reporttype_invalid',$id);
			}
			else
				throw new TConfigurationException('reportservice_class_required',$id);
		}
		else
			throw new THttpException(404,'reportservice_report_unknown',$id);
	}
}
?>