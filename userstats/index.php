<?
//debug
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('../common/Db.php');

class UserStats extends Db {

	public function __construct() {
		parent::__construct();
		echo 'test';
	}


}

$userstats = new UserStats();

/*
count(userLogRequest.param),

select 
userLogRequest.param, 
DAY(userLog._timestamp) as day
from userLog, userLogRequest
where userLog.log_id=userLogRequest.log_id
order by day


select 
userLogRequest.param, 
date(userLog._timestamp) as date
from userLog, userLogRequest
where userLog.log_id=userLogRequest.log_id
group by userLogRequest.param
order by date

2012-05-08
select 
count(userLogRequest.param) as count,
userLogRequest.param
from userLog, userLogRequest
where userLog.log_id=userLogRequest.log_id
and date(userLog._timestamp)='2012-05-08'
group by userLogRequest.param

4 	day
4 	initialer
4 	month
1 	to-day
1 	to-month
6 	to-year
12 	year
*/
?>




