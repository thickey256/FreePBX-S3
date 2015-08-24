<?php 

	include ('classes/config.class.php');
	include ('classes/amazon_upload.class.php');
	
	function remove_directory_junk($source_array)
	{
		//this will remove .. and . and .DS_Store from the directory array.
		//you can add other bits here if you want. may break directory deletion!
		foreach ($source_array as $source_key=>$source)
		{
			if ($source == '.')
			{
				unset($source_array[$source_key]);
			}
			
			if ($source == '..')
			{
				unset($source_array[$source_key]);
			}
			
			if ($source == '.DS_Store')
			{
				unset($source_array[$source_key]);
			}
		}
		return $source_array;
	}
	
	//uploads the file to S3
	function upload_recoding($recording_path, $destination_filename)
	{
		$file_upload = new amazon_upload($recording_path, $destination_filename, 'file');
	}
	
	//Sends a slack notification
	function slack_message($message, $icon = ":phone:")
	{
		$data = "payload=" . json_encode(array(
				"channel"       =>  "#".$GLOBALS['slack_channel'],
				"text"          =>  $message,
				"icon_emoji"    =>  $icon
			));
	
		$ch = curl_init($GLOBALS['hook_url']);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	//get todays date.  I don't what to upload anything for today as calls
	//could still be in progress etc etc.
	$todays_date = new DateTime();
	
	$recording_path = $GLOBALS['recording_path'];
	
	//so lets take a look at the directorys and see where we are at.
	$source_path = scandir($recording_path);
	
	//clean up the array so it's just stuff we want
	$source_path = remove_directory_junk($source_path);
	
	//so now we should have a year or more..
	if (! empty($source_path))
	{
		foreach ($source_path as $year)
		{
			//now we look at the months.
			$year_path = scandir($recording_path.$year);
			
			//clean up the array so it's just stuff we want
			$year_path = remove_directory_junk($year_path);
			
			//and the months
			foreach ($year_path as $month)
			{
				//now we look at the months.
				$month_path = scandir($recording_path.$year.'/'.$month);
				
				//clean up the array so it's just stuff we want
				$month_path = remove_directory_junk($month_path);

				//now the day!
				foreach ($month_path as $day)
				{
					//is this day today.. if so then we don't want to do anythign with it..
					if ($todays_date->format('Y-m-d') != $year.'-'.$month.'-'.$day)
					{
						//now we look at the months.
						$day_path = scandir($recording_path.$year.'/'.$month.'/'.$day);
						
						//clean up the array so it's just stuff we want
						$day_path = remove_directory_junk($day_path);
						
						//so it should now just be case of looping thru each file.
						foreach ($day_path	as $recording)
						{
							$upload_filename = $recording_path.$year.'/'.$month.'/'.$day.'/'.$recording;
							$destination_filename = $year.'/'.$month.'/'.$day.'/'.$recording;
							
							//plonk the file up to amazon
							upload_recoding($upload_filename, $destination_filename);
						}
						
						//now we want to delete the recordings from the server........
						foreach ($day_path	as $recording)
						{
							$upload_filename = $recording_path.$year.'/'.$month.'/'.$day.'/'.$recording;
							unlink($upload_filename);
						}
						
						//now the day should be empty.. so lets kill the directory off.
						rmdir($recording_path.$year.'/'.$month.'/'.$day);

						//now to notify slack
						if ($GLOBALS['notify_slack'] == 1)
						{
							slack_message('Messages for '.$day.'-'.$month.'-'.$year.' have been backed up to Amazon');
						}
					}
				}
				
				//should we delete the month directoy now?
				if (count($month_path) == 0)
				{
					rmdir($recording_path.$year.'/'.$month);
				}
			}
			
			//now look at the year!
			if (count($year_path) == 0)
			{
				echo $year." seems empty";
				rmdir($recording_path.$year);
			}
		}
	}
?>