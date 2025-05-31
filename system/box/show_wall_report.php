<?php
require('../config_session.php');
if(!canManageReport()){
    die();  // Ensure only authorized users can manage reports
}

if(isset($_POST['wall_report'])){
    $id = escape($_POST['wall_report']);
    $report = reportInfo($id);  // Get report details

    // Check if the report is valid
    if(empty($report)){
        echo 1;
        die();
    }

    // Use a prepared statement to safely query the database
    $query = "
        SELECT boom_post.*, boom_users.*
        FROM boom_post
        LEFT JOIN boom_users ON boom_post.post_user = boom_users.user_id
        WHERE boom_post.post_id = ? LIMIT 1
    ";

    if($stmt = $mysqli->prepare($query)) {
        // Bind the parameter for the prepared statement
        $stmt->bind_param('i', $report['report_post']); // 'i' indicates the post_id is an integer
        
        // Execute the statement
        $stmt->execute();
        
        // Get the result
        $get_report = $stmt->get_result();

        // Check if the report was found
        if($get_report->num_rows > 0){
            // Merge the report data with the post data
            $rep = $get_report->fetch_assoc();
            $repp = array_merge($report, $rep);
        }
        else {
            // If no post is found, delete the report and notify staff
            $delete_query = "DELETE FROM boom_report WHERE report_id = ? AND report_type = 2";
            if($del_stmt = $mysqli->prepare($delete_query)) {
                $del_stmt->bind_param('i', $id); // 'i' for integer
                $del_stmt->execute();
                updateStaffNotify();
            }
            echo 1;
            die();
        }

        // Close the statement
        $stmt->close();
    }
    else {
        // Log the error if the statement preparation failed
        error_log("SQL Error: " . $mysqli->error);
        echo "An error occurred while fetching the report.";
        die();
    }
}
else {
    die();  // End execution if no report is provided
}
?>
<div class="pad20">
	<div class="report_content">
		<?php echo showPost($repp['post_id'], 1); ?>
	</div>
	<div class="btable tpad10" id="report_control">
		<div class="bcell report_action">
			<button onclick="removeReport(2,<?php echo $repp['report_id']; ?>, <?php echo $repp['user_id']; ?>);" class="remove_report reg_button delete_btn"><?php echo $lang['do_action']; ?></button>
			<button onclick="unsetReport(<?php echo $repp['report_id']; ?>, 2);" class="unset_report reg_button default_btn"><?php echo $lang['action_none']; ?></button>
		</div>
	</div>
</div>