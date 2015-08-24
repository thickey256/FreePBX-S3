<?php
	require '/var/www/html/calls/library/aws.phar';	
	use Aws\S3\S3Client;

	/* Uploads an image to Amazon S3 and puts the info into a database */
	class amazon_upload
	{	
		private $upload_data;
		private $destination_filename;
		private $upload_type;
		public $amazon_url;
		
		function __construct($upload_data, $destination_filename, $upload_type = 'file')
		{
			$this->upload_data = $upload_data;
			$this->destination_filename = $destination_filename;
			$this->upload_type = $upload_type;
			
			$this->s3_upload();
		}
		
		function s3_upload()
		{
			$new_filename = $this->destination_filename;
			
			//the file I want to upload
			$upload_data = $this->upload_data;

			// Instantiate the client.
			$client = S3Client::factory($GLOBALS['amazon_user']);
			
			if ($this->upload_type == 'file')
			{			
				$amazon_array = array
				(
					'Bucket'		=> $GLOBALS['amazon_bucket'],
					'Key'			=> $new_filename,
					'SourceFile'	=> $upload_data,
					'ContentType'	=> 'text/plain',
					'ACL'			=> 'authenticated-read',
					'StorageClass'	=> 'STANDARD'
				);
			}
			else if ($this->upload_type == 'data')
			{
				$amazon_array = array
				(
					'Bucket'		=> $GLOBALS['amazon_bucket'],
					'Key'			=> $new_filename,
					'Body'			=> $upload_data,
					'ContentType'	=> 'text/plain',
					'ACL'			=> 'authenticated-read',
					'StorageClass'	=> 'STANDARD'
				);
			}

			// Upload a file.
			$result = $client->putObject($amazon_array);
			
			$this->amazon_url = $result['ObjectURL'];
		}
	}		
	
?>