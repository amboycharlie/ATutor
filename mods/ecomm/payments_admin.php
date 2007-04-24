<?php
define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
admin_authenticate(AT_ADMIN_PRIV_ECOMM);

function is_enrolled($member_id, $course_id) {
	global $db;
	$sql = "SELECT approved FROM ".TABLE_PREFIX."course_enrollment WHERE course_id=$course_id AND member_id=$member_id AND approved<>'n'";
	$result = mysql_query($sql, $db);
	return (boolean) mysql_fetch_assoc($result);
}

$sql	= "SELECT COUNT(*) AS cnt FROM ".TABLE_PREFIX."payments WHERE approved=1";
$result = mysql_query($sql, $db);
if (($row = mysql_fetch_assoc($result)) && $row['cnt']) {
	$num_results = $row['cnt'];
} else {
	require(AT_INCLUDE_PATH.'header.inc.php');
	$msg->printInfos('EC_NO_STUDENTS_ENROLLED');
	require(AT_INCLUDE_PATH.'footer.inc.php');
	exit;
}

$results_per_page = 25;
$num_pages = max(ceil($num_results / $results_per_page), 1);
$page = abs($_GET['p']);

if (!$page) {
	$page = 1;
}

$count  = (($page-1) * $results_per_page) + 1;
$offset = ($page-1)*$results_per_page;

/// Get a list of those who have made payments
if ($_GET['reset_filter']) {
	unset($_GET);
}

$page_string = '';


$sql = "SELECT P.*, M.login FROM ".TABLE_PREFIX."payments P INNER JOIN ".TABLE_PREFIX."members M USING (member_id) WHERE P.approved=1 ORDER BY timestamp desc LIMIT $offset, $results_per_page";
$result = mysql_query($sql,$db);

require (AT_INCLUDE_PATH.'header.inc.php'); ?>

<?php print_paginator($page, $num_results, $page_string, $results_per_page); ?>

<table class="data static" summary="">
<thead>
<tr>
	<th scope="col"><?php echo _AT('date'); ?></th>
	<th scope="col"><?php echo _AT('login_name'); ?></th>
	<th scope="col"><?php echo _AT('ec_course_name'); ?></th>
	<th scope="col"><?php echo _AT('enrolled'); ?></th>
	<th scope="col"><?php echo _AT('ec_payment_made'); ?></th>
	<th scope="col"><?php echo _AT('ec_transaction_id'); ?></th>
</tr>
</thead>
<?php while($row = mysql_fetch_assoc($result)): ?>
<tr>
	<td><?php echo $row['timestamp']; ?></td>
	<td><?php echo $row['login']; ?></td>
	<td><?php echo $system_courses[$row['course_id']]['title']; ?></td>
	<td align="center">
		<?php if (is_enrolled($row['member_id'], $row['course_id'])): ?>
			<?php echo _AT('yes'); ?> - <a href="admin/enrollment/enroll_edit.php?id0=<?php echo $row['member_id'].SEP.'func=unenroll'.SEP.'tab=0'.SEP.'course_id='.$row['course_id']; ?>"><?php echo _AT('unenroll'); ?></a>
		<?php else: ?>
			<?php echo _AT('no'); ?> - <a href="admin/enrollment/enroll_edit.php?id0=<?php echo $row['member_id'].SEP.'func=enroll'.SEP.'tab=0'.SEP.'course_id='.$row['course_id']; ?>"><?php echo _AT('enroll'); ?></a>
		<?php endif; ?>
	</td>
	<td align="right"><?php echo $_config['ec_currency_symbol'].number_format($row['amount'], 2); ?> <?php echo $_config['ec_currency']; ?></td>
	<td align="right"><?php echo $row['transaction_id']; ?></td>
</tr>
<?php endwhile; ?>
</table>

<?php require (AT_INCLUDE_PATH.'footer.inc.php'); ?>