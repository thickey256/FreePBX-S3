# FreePBX-S3
a VERY simple method to backup FreePBX call recordings to an S3 bucket

# How to use
Simply change the varialbes in the classes/config.class.php and everything should work from there.

#Other notes
This is designed to be run as a cron task so there is no output. I have added a simple slack notification for each day when successful.