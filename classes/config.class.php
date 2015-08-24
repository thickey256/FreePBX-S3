<?php

	class config
	{
		function __construct()
		{
			//amazon details
			$GLOBALS['amazon_user'] = array
			(
				'key'		=> 'AMAZON KEY HERE',
				'secret'	=> 'AMAZON SECRET HERE',
				'region'	=> 'eu-west-1'
			);
			$GLOBALS['amazon_bucket'] = 'AMAZON BUCKET NAME HERE';
		
			//Path details
			$GLOBALS['recording_path'] = '/var/spool/asterisk/monitor/';  //this is the default location for recordings on FreePBX
		
			//Slack Notification
			//go to https://danetti.slack.com/services/new/incoming-webhook to get a webhook url
			$GLOBALS['notify_slack'] = 0;  //1 to enable 0 to disable
			$GLOBALS['hook_url'] = 'SLACK webhook url here'; //webhook URL
			$GLOBALS['slack_channel'] = 'general'; //the channel you want notifcations to show up in, no need for the # at the start.
		}
	}

	$config = new config();

?>