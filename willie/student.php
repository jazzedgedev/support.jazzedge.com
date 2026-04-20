<?php
$page_title = 'STUDENTS';
include_once('includes/head.php'); 
include_once('includes/header.php');
$active_tab = 'student';
if ($admin_level < 100) { wp_redirect('dashboard.php'); exit; }
include_once('includes/sidebar.php'); 


function get_user_link_views($user_id,$range = 999){
	global $wpdb;
	$user_id = intval($user_id);
	if ($range == 999) {
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM je_link_views WHERE user_id = '$user_id' " );
	} else {
		$start_date = date('Y-m-d', strtotime("- $range DAYS"));
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM je_link_views WHERE user_id = '$user_id' AND (date BETWEEN '$start_date' AND CURRENT_DATE) " );
	}
	if (empty($count)) { return 0; } else {	return $count; }
}

function get_user_video_views($user_id,$range = 999){
	global $wpdb;
	$user_id = intval($user_id);
	if ($range == 999) {
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM je_video_tracking WHERE user_id = '$user_id' " );
	} else {
		$start_date = date('Y-m-d', strtotime("- $range DAYS"));
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM je_video_tracking WHERE user_id = '$user_id' AND (datetime BETWEEN '$start_date' AND CURRENT_DATE) " );
	}
	if (empty($count)) { return 0; } else {	return $count; }
}



$action = (isset($_POST['action'])) ? $_POST['action'] : $_GET['action'];
$email = (isset($_POST['email'])) ? $_POST['email'] : $_GET['email'];
$contact_id = ($_GET['contact_id']) ? $_GET['contact_id'] : $_POST['contact_id'];
global $app;
    if (!$app) {
    	global $install, $app;
		include('/nas/content/live/'.$install.'/keap_isdk/infusion_connect.php');
    }

if ($action == 'lookup_student' || $contact_id > 0) {
	$contact_fields_to_find = array('Id','Email','FirstName','LastName','_AcademyLastLogin','_AcademyExpirationDate','_HSPLastLogin','_SPJLastLogin','_JELastLogin','_Password4Microsites','_JazzedgeExpirationDate','_JEMembershipOfferEndDate','_PurchasedSkus0','_PWWDate2Cancel','_PromoCodesUsed');
	if (!empty($email)) {
		$contact =  $app->findByEmail($email,$contact_fields_to_find);
		$dupe_contacts = (count($contact) > 1) ? ' <span style="color:red;">DUPE FOUND</span>' : '';
		$contact_id = $contact[0]['Id'];
		$contact =  $app->loadCon($contact_id,$contact_fields_to_find);
	} elseif (!empty($_POST['lname'])) { 
		$query = array('LastName' => '%'.$_POST['lname'].'%');
		$contacts = $app->dsQuery("Contact",100,0,$query,$contact_fields_to_find);
	} elseif (!empty($_POST['partial_email'])) { 
		$query = array('Email' => '%'.$_POST['partial_email'].'%');
		$contacts = $app->dsQuery("Contact",100,0,$query,$contact_fields_to_find);
	} elseif (!empty($_POST['id'])) { 
		$user_email = user_email_from_wp_id($_POST['id']);
		$contact =  $app->findByEmail($user_email,$contact_fields_to_find);
		$dupe_contacts = (count($contact) > 1) ? ' <span style="color:red;">DUPE FOUND</span>' : '';
		$contact_id = $contact[0]['Id'];
		$contact =  $app->loadCon($contact_id,$contact_fields_to_find);
	} else {
		$contact =  $app->loadCon($contact_id,$contact_fields_to_find);
	}

	if ($action == 'lookup_student' && isset($contact_id)) {
		wp_redirect('https://jazzedge.academy/willie/ja-admin/student.php?contact_id='.$contact_id); 
		exit;
	}


// START chatgpt update jpc

if ($action === 'reset_jpc_focus_progress') {
    $curriculum_id = isset($_GET['curriculum_id']) ? intval($_GET['curriculum_id']) : null;
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : null;
    if ($curriculum_id !== null) {
        jpc_delete_curriculum_data($user_id, $curriculum_id);
        wp_redirect('https://jazzedge.academy/willie/ja-admin/student.php?contact_id='.$contact_id); 
		exit;
    }
}

if ($action === 'delete_credit_purchase') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;
	$wpdb->delete( 'academy_user_credit_log', array( 'user_id' => $user_id, 'post_id' => $post_id ) );
	header("Location: https://jazzedge.academy/willie/ja-admin/student.php?contact_id=$contact_id");
    exit;
}

if ($action === 'save_credit_purchase_lesson') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
	$data = array(	
		'user_id' => $user_id, 
		'post_id' => intval($_POST['credit_lesson']),
		'timestamp' => time()
	);
	$format = array('%d','%d','%d');
	$wpdb->insert('academy_user_credit_log',$data,$format);
	header("Location: https://jazzedge.academy/willie/ja-admin/student.php?contact_id=$contact_id");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_curriculum_id']) && isset($_POST['end_curriculum_id'])) {
    $start_curriculum_id = intval($_POST['start_curriculum_id']);
    $end_curriculum_id = intval($_POST['end_curriculum_id']);
    $user_id = intval($_POST['user_id']);
    $contact_id = intval($_POST['contact_id']);

    for ($curriculum_id = $start_curriculum_id; $curriculum_id <= $end_curriculum_id; $curriculum_id++) {
        // Prepare the data to update or insert in jpc_student_progress
        $data = [
            'user_id' => $user_id,
            'curriculum_id' => $curriculum_id
        ];
        for ($step = 1; $step <= 12; $step++) {
            $column_name = "step_$step";
            $data[$column_name] = $step + (($curriculum_id - 1) * 12);
        }

        // Check if a record exists for this curriculum_id in jpc_student_progress
        $existing_record = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM jpc_student_progress WHERE user_id = %d AND curriculum_id = %d",
            $user_id, $curriculum_id
        ));

        // Insert or update the record in jpc_student_progress
        if ($existing_record == 0) {
            $wpdb->insert('jpc_student_progress', $data, array_merge(['%d', '%d'], array_fill(0, 12, '%d')));
        } else {
            $wpdb->update('jpc_student_progress', $data, ['user_id' => $user_id, 'curriculum_id' => $curriculum_id], array_merge(array_fill(0, 12, '%d')), ['%d', '%d']);
        }

        // Insert records into jpc_student_assignments
        for ($step = 1; $step <= 12; $step++) {
            $wpdb->insert(
                'jpc_student_assignments',
                [
                    'user_id' => $user_id,
                    'date' => current_time('mysql'),
                    'step_id' => $step + (($curriculum_id - 1) * 12),
                    'curriculum_id' => $curriculum_id,
                    'completed_on' => current_time('mysql'),
                    'deleted_at' => NULL
                ],
                [
                    '%d',
                    '%s',
                    '%d',
                    '%d',
                    '%s',
                    '%s'
                ]
            );
        }
    }

    echo "Progress for curriculum IDs $start_curriculum_id to $end_curriculum_id marked as complete.";
    // Refresh the page to update the progress
    header("Location: https://jazzedge.academy/willie/ja-admin/student.php?contact_id=$contact_id");
    exit;
}

// END chatgpt update jpc

	$returnFields = array( 'ContactId', 'Id', 'JobId', 'PayStatus', 'Description', 'ProductSold', 'TotalDue', 'TotalPaid');
	$invoiceDetails = $app->dsLoad("Invoice", $contact_id, $returnFields);


	$returnFields = array( 'ContactId', 'Id', 'JobId', 'PayStatus', 'Description', 'ProductSold', 'TotalDue', 'TotalPaid','DateCreated');
	$query = array('ContactId' => $contact_id, 'PayStatus' => 0);
	$failed_invoices = $app->dsQuery("Invoice",100,0,$query,$returnFields);

	$returnFields = array( 'ContactId', 'Id', 'PayPlanStatus', 'PayStatus', 'Description', 'ProductSold', 'TotalDue', 'TotalPaid','DateCreated');
	$query = array('ContactId' => $contact_id, 'PayStatus' => 1);
	$paid_invoices = $app->dsQuery("Invoice",100,0,$query,$returnFields);


	$returnFields = array( 'ContactId', 'ChargeId', 'Id', 'InvoiceId', 'PayAmt', 'PayDate', 'PayType', 'RefundId', 'ChargeId', 'LastUpdated');
	$query = array('ContactId' => $contact_id);
	$payments = $app->dsQuery("Payment",100,0,$query,$returnFields);


	// credit cards (not needed??)
	$returnFields = array( 'Id', 'ContactId', 'CardType', 'ExpirationMonth', 'ExpirationYear', 'Status');
	$query = array('ContactId' => $contact_id);
	$credit_cards = $app->dsQuery("CreditCard",100,0,$query,$returnFields);


	$returnFields = array( 'ContactId', 'ContactGroup', 'GroupId', 'DateCreated', 'Contact.Groups','Contact.ContactNotes');
	$query = array('ContactId' => $contact_id);
	$tags = $app->dsQuery("ContactGroupAssign",400,0,$query,$returnFields);
	$tag_ids = $tags[0]['Contact.Groups'];
	$contact_notes = $tags[0]['Contact.ContactNotes'];

	//CHECK TAGS

	$tag_ids_array = explode (',',$tag_ids);
	$je_access = (array_intersect(array(8649,8645,8777,8811,8817),$tag_ids_array) && empty(array_intersect(array(8701,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';
	$nbp_access = (array_intersect(array(8867,8859,8869,8861),$tag_ids_array) && empty(array_intersect(array(8701,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';
	$pww_access = (array_intersect(array(7056,8276),$tag_ids_array) && empty(array_intersect(array(7060,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';
	$hsp_access = (array_intersect(array(8326,7548),$tag_ids_array) && empty(array_intersect(array(7552,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';
	$cpl_access = (array_intersect(array(8318,6772),$tag_ids_array) && empty(array_intersect(array(6776,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';
	$rpl_access = (array_intersect(array(6816,8320),$tag_ids_array) && empty(array_intersect(array(6820,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';
	$fpl_access = (array_intersect(array(8322,6808),$tag_ids_array) && empty(array_intersect(array(6812,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';
	$pbp_access = (array_intersect(array(8324,6826),$tag_ids_array) && empty(array_intersect(array(6830,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';
	$jpt_access = (array_intersect(array(8316,6782),$tag_ids_array) && empty(array_intersect(array(6786,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';
	$mto_access = (array_intersect(array(8314,6836),$tag_ids_array) && empty(array_intersect(array(6840,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';
	$academy_access = (array_intersect(array(9659,9657,9653),$tag_ids_array) && empty(array_intersect(array(9717,7772),$tag_ids_array))) ? '<strong class="access_yes">Yes</strong>' : '<strong class="access_no">No</strong>';


	$returnFields = array( 'ContactId', 'Id', 'AutoCharge', 'BillingAmt', 'BillingCycle', 'LastBillDate', 'PaidThruDate', 'ProductId','StartDate', 'Status', 'BillingCycle','MerchantAccountId','MaxRetry','NumDaysBetweenRetry','PaymentGatewayId','ReasonStopped','SubscriptionPlanId','OriginatingOrderId','NextBillDate');
	$query = array('ContactId' => $contact_id);
	$subscriptions = $app->dsQuery("RecurringOrder",100,0,$query,$returnFields);

//	$opt_status = ($app->optStatus($contact['Email']) > 0) ? 'Opt-in' : '(<strong style="color:red;">Opt-out</strong>) ';
}

if ($action === 'delete-osop-suggestion') {
	$user_id = intval($_GET['user_id']);
	$wpdb->delete( 'academy_osop_suggestions', array( 'ID' => intval($_GET['id']), 'user_id' => $user_id ) );
}

if ($action === 'osop_ready') {
	$user_id = intval($_GET['user_id']);
	update_user_meta($user_id, 'osop_ready', 'yes');
	update_user_meta($user_id, 'osop_booked', 'yes');
	update_user_meta($user_id, 'academy_osop_emailed', 'yes');
	update_user_meta($user_id, 'osop_committed', 'yes');
	$msg = 'OSOP Ready';
}

if ($action === 'write_osop_call_lesson_suggestions') {
	$user_id = intval($_POST['user_id']);
	$keap_id = intval($_POST['keap_id']);
	$url = 'https://jazzedge.academy/wp-json/uap/v2/uap-4338-4339'; //uncanny automator
	$user_email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
	$emailed = $wpdb->get_var( "SELECT ID FROM academy_osop_emailed WHERE user_id = $user_id " );
	$osop_notes = isset($_POST['osop_notes']) ? sanitize_text_field($_POST['osop_notes']) : '';

	$wpdb->query(
		$wpdb->prepare(
			"UPDATE academy_osop_emailed SET to_do = NULL WHERE user_id = %d",
			$user_id
		)
	);

	if (empty($emailed)) {
		$response = wp_remote_post($url, array(
			'method' => 'POST',
			'body' => array(
				'code' => 'jDfwkAchj3Fs1kY',
				'user_email' => $user_email,
			)
		));

		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";
		} else {
			$data = array(	
				'user_id' => $user_id, 
				'user_email' => $user_email,
				'notes' => $osop_notes,
				'date' => date('Y-m-d'),
				'to_do' => NULL,
			);
			$format = array('%d','%s','%s','%s');
			$wpdb->insert('academy_osop_emailed',$data,$format);
		}
		
		update_user_meta($user_id,'osop_emailed_need_to_book','yes');
		
		keap_update_contact($keap_id,array('_AcademyExpirationDate' => ''));
		
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE wp_fluentform_submissions
				 SET status = %s
				 WHERE response LIKE %s",
				'osop_complete', // The new status
				'%' . $wpdb->esc_like($user_email) . '%' // Search email in the response
			)
		);
	}
	
	
	if (isset($_POST['dc_course']) && !empty($_POST['dc_course'])) {
		$d = array();
		$d = explode('**',$_POST['dc_course']);
		$data = array(	
			'user_id' => $user_id, 
			'title' => sanitize_text_field($d[1]),
			'type' => 'course',
			'url' => sanitize_url($d[0]),
		);
		$format = array('%d','%s','%s','%s');
		$wpdb->insert('academy_osop_suggestions',$data,$format);
	}
	if (isset($_POST['dc_premier_course']) && !empty($_POST['dc_premier_course'])) {
		$d = array();
		$d = explode('**',$_POST['dc_premier_course']);
		$data = array(	
			'user_id' => $user_id, 
			'title' => sanitize_text_field($d[1]),
			'type' => 'premier-course',
			'url' => sanitize_url($d[0]),
		);
		$format = array('%d','%s','%s','%s');
		$wpdb->insert('academy_osop_suggestions',$data,$format);
	}
	if (isset($_POST['dc_lesson']) && !empty($_POST['dc_lesson'])) {
		$d = array();
		$d = explode('**',$_POST['dc_lesson']);
		$data = array(	
			'user_id' => $user_id, 
			'title' => sanitize_text_field($d[1]),
			'type' => 'lesson',
			'url' => sanitize_url($d[0]),
		);
		$format = array('%d','%s','%s','%s');
		$wpdb->insert('academy_osop_suggestions',$data,$format);
	}
	if (isset($_POST['dc_class']) && !empty($_POST['dc_class'])) {
		$d = array();
		$d = explode('**',$_POST['dc_class']);
		$data = array(	
			'user_id' => $user_id, 
			'title' => sanitize_text_field($d[1]),
			'type' => 'class',
			'url' => sanitize_url($d[0]),
		);
		$format = array('%d','%s','%s','%s');
		$wpdb->insert('academy_osop_suggestions',$data,$format);
	}
	if (!isset($_POST['back_to_osop'])) {
		wp_redirect('https://jazzedge.academy/willie/ja-admin/student.php?contact_id='.$contact_id); 
	} else { wp_redirect('https://jazzedge.academy/willie/ja-admin/osop_submissions.php'); }
	exit;
}

// Check if form was submitted and action is correct
if ($action === 'write_osop_followup') {
    // Sanitize and get form data
    $user_id = intval($_POST['user_id']);
    $note = sanitize_textarea_field($_POST['note']);
    $interest_level = intval($_POST['interest_level']);
    $osop_update = intval($_POST['osop_update']); // Check if it's an update

    // If it's an update, perform an UPDATE query
    if ($osop_update === 1) {
        $updated = $wpdb->update(
            'academy_osop_followup', // Table name
            array(
                'note' => $note,
                'interest_level' => $interest_level
            ),
            array('user_id' => $user_id), // Where condition
            array(
                '%s',  // note (string)
                '%d'   // interest_level (integer)
            ),
            array('%d') // Where condition format (user_id as integer)
        );

        if ($updated !== false) {
            wp_redirect('https://jazzedge.academy/willie/ja-admin/student.php?contact_id='.$contact_id); 
			exit;
        } else {
            echo "Error updating follow-up.";
        }

    // If it's not an update, perform an INSERT query
    } else {
        $inserted = $wpdb->insert(
            'academy_osop_followup',  // Table name
            array(
                'user_id' => $user_id,
                'note' => $note,
                'interest_level' => $interest_level
            ),
            array(
                '%d',  // user_id (integer)
                '%s',  // note (string)
                '%d'   // interest_level (integer)
            )
        );

        if ($inserted) {
            wp_redirect('https://jazzedge.academy/willie/ja-admin/student.php?contact_id='.$contact_id); 
			exit;
        } else {
            echo "Error saving follow-up.";
        }
    }
}
if ($action == 'delete_quiz') {
	$wpdb->delete( 'academy_premier_quiz_results', array( 'ID' => intval($_GET['id']), 'user_id' => intval($_GET['user_id']) ) );
	wp_redirect('https://jazzedge.academy/willie/ja-admin/student.php?msg=Quiz-Deleted&contact_id='.$contact_id); 
	exit;
}

if ($action == 'update_password') {
	$email = $_POST['email'];
	$password = $_POST['password'];
	$user_id = intval($_POST['user_id']);
	if (!empty($password) AND $user_id > 0 AND !empty($email)) {
	wp_set_password( $password, $user_id );
	wp_redirect('https://jazzedge.academy/willie/ja-admin/student.php?action=lookup_student&email='.$email.'&msg=Password Updated');
	exit;
	} else {
	wp_redirect('https://jazzedge.academy/willie/ja-admin/student.php?errmsg=Error Updating Password');
	exit;
	}
}

if ($action === 'grant_access_premier_course') {
	$user_id = intval($_POST['user_id']);
	$email = sanitize_email($_POST['email']);
	$premier_course_sku = sanitize_text_field($_POST['premier_course_sku']);
	$found = $wpdb->get_var( "SELECT ID FROM academy_event_purchased WHERE user_id = $user_id AND event_sku = '$premier_course_sku' " );
	if ($found) { $msg = "They already have access to this Premier Course"; }
	else {
		$data = array(	
			'user_id' => $user_id, 
			'event_sku' => $premier_course_sku,
			'date_purchased' => date('Y-m-d'),
			'user_email' => $email,
			'note' => 'add via admin',
		);
		$format = array('%d','%s','%s','%s','%s');
		$wpdb->insert('academy_event_purchased',$data,$format);
		$msg = 'User Granted Access to Premier Course';
	}
}


if ($action == 'add_credits') {
 $wp_userid = je_get_user_id_by_email($contact['Email']);
 	$q = $wpdb->get_results( "SELECT * FROM academy_user_credits WHERE user_id = $wp_userid " );
 	if (empty($q)) {
 			$data = array(	'user_id' => $wp_userid, 
					'class_credits' => intval($_POST['credits_to_add']),
					'keap_id' => $contact_id,
				);
	$format = array('%d','%d','%d');
	$wpdb->insert('academy_user_credits',$data,$format);
 	} else {
 		$updated_credits = $q[0]->class_credits;
		$wpdb->update('academy_user_credits', array(
				'class_credits' => $updated_credits + intval($_POST['credits_to_add']),
		),array ('user_id' => $wp_userid));
	}
	

	// insert into credit promotion database as well
	$data = array(	
		'user_id' => $wp_userid, 
		'credits_received' => intval($_POST['credits_to_add']),
		'note' => 'added via admin area',
		'date' => date('Y-m-d'),
	);
	$format = array('%d','%d','%s','%s');
	$wpdb->insert('academy_credit_promotion',$data,$format);

	wp_redirect('?contact_id=' . $contact_id);
	exit;
}


if ($action == 'remove_hsp_child_access') {
		$contacts_not_tagged = array();
		$contacts_tagged = array();
		$parent_emails_array = explode(PHP_EOL, $_POST['hsp_parent_emails']);
		$contact_fields_to_find = array('Id','Email','_HSPChildParentMatch');
		foreach ($parent_emails_array as $parent_email_address) {
			$contact =  $app->findByEmail(trim($parent_email_address),$contact_fields_to_find);
			$parent_id = $contact[0]['Id'];
			$hsp_parent_child_match = substr($contact[0]['_HSPChildParentMatch'],5);
			if ($parent_id > 0 && !empty($hsp_parent_child_match)) { 
				// find child accounts				
				$query = array('_HSPChildParentMatch' => '%chld-'.$hsp_parent_child_match.'%');
				$children = $app->dsQuery("Contact",100,0,$query,$contact_fields_to_find);
					foreach ($children AS $child) {
						$child_id = $child['Id'];
						// 9553	HSBC Remove Access
						// 9555	HSP Child Account Removed
						$app->grpAssign($child_id,9553); 
						$app->grpAssign($child_id,9555); 
						$contacts_tagged[] .= $child['Email'] . " (Parent email - $parent_email_address)";
					}
			} 
			else { $contacts_not_tagged[] .= $email_address; }
		}
		$msg = 'HSP Child Accounts Removed';

		//exit;
} 

if ($action == 'remove_hsp_access') {
		$contact_fields_to_find = array('Id','Email','FirstName','LastName','_HSPLastLogin');
		$match_codes = explode("\n", $_POST['hsp_parent_match_code']); // or use PHP PHP_EOL constant
		foreach ($match_codes AS $match_code) {
			$match_code = substr($match_code,5);
			echo "code = $match_code<br>";
			if ($match_code != '') { 
				$query = array('_HSPChildParentMatch' => '%'.$match_code.'%');
				$results = $app->dsQuery("Contact",10,0,$query,$contact_fields_to_find);
				//echo '<pre><h2>$results</h2>'; print_r($results); echo '</pre>';
				foreach ($results AS $result) {
					//echo '<pre><h2>$result</h2>'; print_r($result); echo '</pre>';
					$id = $result['Id'];
					$contact_email = $result['Email'];
					$contact_name = $result['FirstName'].' '.$result['LastName'];
					$tag_id = $_POST['tag_id'];
					$app->grpAssign($id, $tag_id);
					echo "<p>Tag ($tag_id) set for $contact_name ($id - $contact_email)</p>";
				}
			}
		}
		$msg = 'HSP Access Removed';
		//exit;
} 

if ($action == 'bulk_tag') {
		$contacts_not_tagged = array();
		$contacts_tagged = array();
		$emails = $_POST['emails'];
		$tag_id = $_POST['tag_id'];
		$emails_array = explode(PHP_EOL, $emails);

		$contact_fields_to_find = array('Id');		

		foreach ($emails_array as $email_address) {
			usleep(100);
			$contact =  $app->findByEmail(trim($email_address),array('Id'));
			$cid = $contact[0]['Id'];
			if ($cid > 0) { 
				$app->grpAssign($cid, $tag_id); 
				$contacts_tagged[] .= $email_address;
			} 
			else { $contacts_not_tagged[] .= $email_address; }
		}
		$msg = 'Bulk Tags Applied';
} 


if ($action == 'optin') {
		$contacts_not_tagged = array();
		$contacts_tagged = array();
		$emails = $_POST['emails'];
		$tag_id = 9587;
		$emails_array = explode(PHP_EOL, $emails);

		$contact_fields_to_find = array('Id');		

		foreach ($emails_array as $email_address) {
			if (!empty($email_address)) { 
				//$email_address = 'ar.george@gmail.com';
				echo "email= $email_address<br>";
				$result = $app->optIn($email_address, 'Contact was removed by mistake via API. They requested to be on the list.'); 
				echo '<pre><h2>$result</h2>'; print_r($result); echo '</pre>';
				//$result2 = $app->grpAssign($cid, $tag_id); 
				//echo '<pre><h2>$result</h2>'; print_r($result2); echo '</pre>';
				exit;
				$contacts_tagged[] .= $email_address;
			} 
			else { $contacts_not_tagged[] .= $email_address; }
		}
		$msg = 'Contacts Opt-in Updated';
} 


if (!empty($contacts_tagged)) {
	$tagged_result = '<h1>TAGGED</h1>';
	$tagged_result .= '<ul>';
	foreach ($contacts_tagged AS $contact_tagged) {
		$tagged_result .= "<li>$contact_tagged</li>";
	}
	$tagged_result .= '</ul>';
}

if (!empty($contacts_not_tagged)) {
	$not_tagged_result = '<h1>NOT TAGGED (Not Found in Infusionsoft)</h1>';
	$not_tagged_result .= '<ul>';
	foreach ($contacts_not_tagged AS $contact_not_tagged) {
		$not_tagged_result .= "<li>$contact_not_tagged</li>";
	}
	$not_tagged_result .= '</ul>';
}


// LAST LOGIN
$pww_last_login = (!empty($contact['_PWWLastLogin'])) ? convert_infusion_date($contact['_PWWLastLogin']) : '';
$hsp_last_login = (!empty($contact['_HSPLastLogin'])) ? convert_infusion_date($contact['_HSPLastLogin']) : '';
$fpl_last_login = (!empty($contact['_FPLLastLogin'])) ? convert_infusion_date($contact['_FPLLastLogin']) : '';
$rpl_last_login = (!empty($contact['_RPLLastLogin'])) ? convert_infusion_date($contact['_RPLLastLogin']) : '';
$pbp_last_login = (!empty($contact['_PBPLastLogin'])) ? convert_infusion_date($contact['_PBPLastLogin']) : '';
$jpt_last_login = (!empty($contact['_JPTLastLogin'])) ? convert_infusion_date($contact['_JPTLastLogin']) : '';
$mto_last_login = (!empty($contact['_MTOLastLogin'])) ? convert_infusion_date($contact['_MTOLastLogin']) : '';
$cpl_last_login = (!empty($contact['_CPLLastLogin'])) ? convert_infusion_date($contact['_CPLLastLogin']) : '';
$spj_last_login = (!empty($contact['_SPJLastLogin'])) ? convert_infusion_date($contact['_SPJLastLogin']) : '';
$je_last_login = (!empty($contact['_JELastLogin'])) ? convert_infusion_date($contact['_JELastLogin']) : '';
$academy_last_login = (!empty($contact['_AcademyLastLogin'])) ? convert_infusion_date($contact['_AcademyLastLogin']) : '';

?>	


<div id="content">

<?php include_once('includes/messages.php'); ?>


	<?php if ($contact_id < 1 && empty($contacts)) { ?>
	<?php if ($contact_id < 1 && isset($_POST['email'])) { ?>
	<div class="message errormsg"><p>Student Not Found. <?php echo $_POST['email']; ?></p></div>
	<?php } ?>
	<h2>Reports</h2>
	<ul>
	<li><a href="saved-report-1623.php">Jazzedge Cancelled But Status Active</a></li>
	</ul>
	<h2>Student Search:</h2>
	<form action="" method="post">
			<p>
				<label>Email Address:</label><br />
				<input type="text" name="email" size="50" value="<?php echo $email; ?>" onclick="select()" class="text">
			</p>
			<p>
				<label>Email Address (Partial):</label><br />
				<input type="text" name="partial_email" size="50" value="<?php echo $email; ?>" onclick="select()" class="text">
			</p>			<p>
				<label>Last Name:</label><br />
				<input type="text" name="lname" size="50" value="" onclick="select()" class="text">
			</p>
			<p>
				<label>Wordpress ID:</label><br />
				<input type="text" name="id" size="50" value="" onclick="select()" class="text">
			</p>
			<p>
				<input type="hidden" name="action" value="lookup_student">
				<input type="submit" class="submit" value="Find" />
			</p>
	</form>
	
	<table>
	<tr>
	<td valign="top">
	
	<h2>Bulk Remove HSP Access</h2>
	<form method="post" action="">
		<p>
		Enter one match code per line (not CSV). No need to remove 'prnt' from the front end.
		</p>
		<textarea rows="10" cols="60" onclick="" name="hsp_parent_match_code"><?php echo $_POST['hsp_parent_match_code']; ?></textarea><br>
		<br>
		Tag ID: <input type="text" name="tag_id" value="<?php echo $_POST['tag_id']; ?>"> (what tag id should be set?)
		<input name="action" type="hidden" value="remove_hsp_access" />
		<input type="submit" class="submit">
		<p>Common Tags:</p>
		<ul>
		<li>9075-HSBC Halt Access</li>
		</ul>
	</form>
	</td>
	<td valign="top">
	<h2>Bulk Tag Contacts</h2>
	<form method="post" action="">
		<p>
		Enter one email address per line.
		</p>
		<textarea rows="10" cols="60" onclick="" name="emails"></textarea><br>
		<br>
		Tag ID: <input type="text" name="tag_id" value="<?php echo $_POST['tag_id']; ?>"> (what tag id should be set?)
		<input name="action" type="hidden" value="bulk_tag" />
		<input type="submit" class="submit">
	</form>	
	<p>Common Tags:</p>
		<ul>
		<li>9553-HSBC Remove Access</li>
		</ul>
	</td>
	</tr>
	<tr>
	<td valign="top">
	
	<h2>Remove Child Account Access</h2>
	<form method="post" action="">
		<p>
		Enter one parent email address per line.
		</p>
		<textarea rows="10" cols="60" onclick="" name="hsp_parent_emails"></textarea><br>
		<br>
		<input name="action" type="hidden" value="remove_hsp_child_access" />
		<input type="submit" class="submit">
	</form>
	</td>
	<td valign="top">
	<h2>Bulk Opt-in Contacts</h2>
	<form method="post" action="">
		<p>
		Enter one email address per line.
		</p>
		<textarea rows="10" cols="60" onclick="" name="emails"></textarea><br>
		<br>
		<input name="action" type="hidden" value="optin" />
		<input type="submit" class="submit">
	</form>	
	</td>
	</tr>
	</table>
	<?php echo $tagged_result; ?>
	<?php echo $not_tagged_result; ?>	
	<br><br><br>

	<?php } elseif (!empty($contacts)) { ?>
	<h2>Contacts</h2>
	<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
	<thead>
	<tr>
		<th>Id</th>
		<th>Name</th>
		<th>Email</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
		<?php 
		foreach ($contacts AS $c) {
			echo "<tr>
			<td><a href='https://ft217.infusionsoft.com/Contact/manageContact.jsp?view=edit&amp;ID=$c[Id]&lists_sel=orders' target='_blank'>$c[Id]</a></td>
			<td><a href='?contact_id=$c[Id]&action=find'>$c[FirstName] $c[LastName]</a></td>
			<td>$c[Email]</td>
			<td></td>
			</tr>";
		}
		?>
	</tbody>				
</table>
	
	<?php } else { ?>
	<?php 
	
	if (in_array(2570,$tag_ids_array)) { echo '<div class="message error">BLOCKED</div>'; }

	$wp_uid = je_get_user_id_by_email($contact['Email']);
	$user_id = $wp_uid;
	$email = $contact['Email'];
	$query = $wpdb->prepare("SELECT id FROM `wp_fs_persons` WHERE `email` LIKE '%s'", $email );
	$fluent_id = $wpdb->get_var($query);
	$password = $contact['_Password4Microsites'];
	$contact_name =  $contact['FirstName'] . ' ' . $contact['LastName'];
	$je_exp_date = (isset($contact['_JazzedgeExpirationDate'])) ? convert_infusion_date($contact['_JazzedgeExpirationDate']) : '';
	//$academy_exp_date2 = (isset($contact['_AcademyExpirationDate'])) ? convert_infusion_date($contact['_AcademyExpirationDate']) : '';
	$academy_exp_date = (isset($contact['_AcademyExpirationDate'])) ? date("m/d/Y",strtotime($contact['_AcademyExpirationDate'])) : '';
	$academy_last_login = (isset($contact['_AcademyLastLogin'])) ? convert_infusion_date($contact['_AcademyLastLogin']) : '';

	echo '<h1>' . $contact['FirstName'] . ' ' . $contact['LastName'] . $dupe_contacts . '</h1>';

?>
<input type="text" onclick="select()" size="40" style="font-size:14pt; margin-bottom:10px; padding: 5px;" value="<?php echo $contact['Email']; ?>">  (<?php echo $opt_status; ?>)

<?php
echo '<p>
<a href="student_tracking_data.php?user_id='.$wp_uid.'&keap_id='.$contact_id.'&name='.$contact_name.'">View Student Data</a> &bull;

(<a href="https://ft217.infusionsoft.com/Contact/manageContact.jsp?view=edit&amp;ID='.$contact_id.'&lists_sel=orders" target="_blank">View in Infusionsoft</a>)
(<a href="https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id='.$contact_id.'&Email='.$contact['Email'].'&redir=/dashboard/" target="_blank">View in Jazzedge Academy</a>) 
(<a href="https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id='.$contact_id.'&Email='.$contact['Email'].'&redir=/jazzedge-upgrade?upgrade=2" target="_blank">Academy Upgrade</a>) 
(<a href="https://myjazzedge.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id='.$contact_id.'&Email='.$contact['Email'].'&redir=/sites/" target="_blank">View in MyJazzedge</a>) 
(<a href="https://myjazzedge.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id='.$contact_id.'&Email='.$contact['Email'].'&redir=/downloads/" target="_blank">MyJazzedge Downloads</a>) 
(<a href="https://jazzedge.academy/?memb_autologin=yes&auth_key=K9DqpZpAhvqe&Id='.$contact_id.'&Email='.$contact['Email'].'&redir=/billing/" target="_blank">CCard Update</a>) 
(<a href="https://homeschoolpiano.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id='.$contact_id.'&Email='.$contact['Email'].'&redir=/dashboard/" target="_blank">View in HSP</a>) 
(<a href="https://jazzpianolessons.com/?memb_autologin=yes&auth_key=9JeYjtLTtj3G&Id='.$contact_id.'&Email='.$contact['Email'].'&redir=/dashboard/" target="_blank">View in JPL</a>) 
(<a href="https://jazzedge.academy/wp-admin/users.php?s='.$contact['Email'].'" target="_blank">Find in Wordpress</a>)</p>';
?>


<form method="post">
	<input type="hidden" name="action" value="update_password" />
	<input type="hidden" name="user_id" value="<?php echo $wp_uid; ?>" />
	<input type="hidden" name="email" value="<?php echo $contact['Email']; ?>" />
	Set Academy Password: <input type="text" name="password" value="" />
	<button type="submit" class="button blue small">Set Password</button> Random pass: <input type="text" onclick="select()" size="10" value="<?php echo random_password(); ?>" />
</form>
<p>
Password: <?php echo $password; ?><br />
JE Expiration Date: <?php echo $je_exp_date; ?> (<a href="actions.php?action=clear-je-expiration&contact_id=<?php echo $contact_id; ?>">clear</a>)<br />
<form method="post" action="actions.php">
	<input type="hidden" name="action" value="set_academy_exp_date" />
	<input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
	Academy Expiration Date: <input type="text" name="academy_exp_date" id="datepicker" class="datepicker" value="<?php echo $academy_exp_date; ?>" />
	<button type="submit" class="button blue small">Set</button> (<a href="actions.php?action=clear-academy-expiration&contact_id=<?php echo $contact_id; ?>">clear</a>)

</form>
<br />
Academy Last Login: <?php echo $academy_last_login; ?>
<br>
Wordpress User Id: <?php echo $wp_uid; ?>
<br>
Infusionsoft (Keap) ID: <?php echo $contact_id; ?>
<br>
Fluent ID: <?php echo $fluent_id; ?>

<?php if ($wp_uid < 1) { ?>
<a href="actions.php?action=give-ja-access&contact_id=<?php echo $contact_id; ?>"><button type="submit" class="je-button">Setup Academy Free Access</button></a>
<?php } ?>

<?php 
global $wpdb;
$q = $wpdb->get_results( "SELECT * FROM academy_user_credits WHERE user_id = $wp_uid" );
$users_credits = $q[0]->class_credits;
?> 
<p>
<form method="post">
	<input type="hidden" name="action" value="add_credits" />
	<input type="hidden" name="user_id" value="<?php echo $wp_uid; ?>" />
	<p>
	<span style="font-size: 12pt; padding: 6px; border:1px solid #ccc;"><strong>Academy Credits:</strong> <?php echo $users_credits; ?></span>
	</p>

	Add this many credits: <input type="text" name="credits_to_add" size="4" value="" />
	<button type="submit" class="button blue small">Update</button> (use a negative number to delete credits)
</form>
</p>
<p>
<a href="actions.php?action=academy-add-three-credits&contact_id=<?php echo $contact_id; ?>" onclick="return confirm('Are you sure you want to give them 3 Academy Credits?')" class="medium button green">Give Them 3 Academy Credits</a>
</p>

<?php 
$promo_credits = $wpdb->get_results( "SELECT * FROM academy_credit_promotion WHERE user_id = $wp_uid " );

$credits_used = $wpdb->get_results( "SELECT * FROM academy_user_credit_log WHERE user_id = $wp_uid " );

$premier_courses = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_type = 'premier-course' AND post_status = 'publish' " );
 ?>

<h1>JPC Progress for User ID: <?php echo $user_id; ?></h1>
<div class='rg-container hover-black'>
    <table class='rg-table zebra' summary='Hed'>
        <thead>
            <tr>
                <th class='text'>ID</th>
                <th class='text' width="40%">Focus</th>
                <th class='text center'>C</th> 
                <th class='text center'>F</th>
                <th class='text center'>G</th>
                <th class='text center'>D</th>
                <th class='text center'>Bb</th>
                <th class='text center'>A</th>
                <th class='text center'>Eb</th>
                <th class='text center'>E</th>
                <th class='text center'>Ab</th>
                <th class='text center'>Db</th>
                <th class='text center'>F#</th>
                <th class='text center'>B</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $query = $wpdb->prepare("
            SELECT jp.*, jpc.focus_title
            FROM jpc_student_progress jp
            JOIN je_practice_curriculum jpc ON jp.curriculum_id = jpc.ID
            WHERE jp.user_id = %d
            ORDER BY jp.curriculum_id ASC
        ", $user_id);
        $q = $wpdb->get_results($query);
        foreach ($q as $r) {
            $curriculum_id = $r->curriculum_id;
            $focuses_completed = jpc_check_all_steps_complete($user_id,$curriculum_id);
            $ja_milestone_graded = ja_milestone_graded($curriculum_id);
            echo '<tr>';
            echo '<td><a href="?action=reset_jpc_focus_progress&curriculum_id='.$curriculum_id.'&user_id='.$user_id.'&contact_id='.$contact_id.'" onclick="return confirm(\'Are you sure you want to delete all of your progress for this practice focus? This will delete EVERYTHING from ID: '.$curriculum_id.' and GREATER. For example, if you delete ID #2 and you have progress for ID #4,5 and 6, this will delete ID #2-6.\n\n(This can not be undone)\')"><i class="fa-sharp fa-solid fa-trash"></i></a> '.$curriculum_id.'</td>';
            echo '<td>'.$r->focus_title;
            if (!empty($ja_milestone_graded)) {
                echo $ja_milestone_graded;
            }
            echo '</td>';
            for ($i = 1; $i <= 12; $i++) {
                $step_column = 'step_' . $i;
                $jpc_status = is_null($r->$step_column) ? 'incomplete' : 'complete';
                $status_color = ($jpc_status == 'incomplete') ? 'gray' : 'green';
              //  $jpc_step_id = ja_jpc_step_id($curriculum_id, $i);
                $jpc_link = ($jpc_status == 'incomplete') ? '' : '<a href="/jpc-lesson/?step_id=' . $r->$step_column . '&cid=' . $curriculum_id . '" target="_blank" class="">';
                $jpc_link_close = !empty($jpc_link) ? '</a>' : '';
                echo '<td class="center">' . $jpc_link . '<i class="fa-sharp ' . $status_color . ' fa-solid fa-circle-play"></i>' . $jpc_link_close . '</td>';
            }
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
</div>

    <h1>Update JPC Progress for User ID: <?php echo $user_id; ?></h1>

    <form method="post">
        <label for="start_curriculum_id">Select Start Curriculum ID:</label>
        <select name="start_curriculum_id" id="start_curriculum_id">
            <?php for ($curriculum_id = 1; $curriculum_id <= 50; $curriculum_id++): ?>
                <option value="<?php echo $curriculum_id; ?>"><?php echo $curriculum_id; ?></option>
            <?php endfor; ?>
        </select>

        <label for="end_curriculum_id">Select End Curriculum ID:</label>
        <select name="end_curriculum_id" id="end_curriculum_id">
            <?php for ($curriculum_id = 1; $curriculum_id <= 50; $curriculum_id++): ?>
                <option value="<?php echo $curriculum_id; ?>"><?php echo $curriculum_id; ?></option>
            <?php endfor; ?>
        </select>

        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
        <input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
        <button type="submit">Mark All Selected Steps Complete</button>
    </form>
    

<h2>OSOP Lesson Suggestions:</h2>

<?php
// Load WordPress environment and global variables
global $wpdb;
$email = $contact['Email'];
// Prepare and execute the query to find the submission where the response contains the email
$submission = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM wp_fluentform_submissions WHERE status = ('unread' || 'read') AND form_id = 35 AND response LIKE %s ORDER BY ID DESC LIMIT 1",
        '%' . $wpdb->esc_like($email) . '%'
    )
);
// Check if a submission was found
if ($submission) {
    // Decode the JSON response field
    $form_data = json_decode($submission->response, true);

    // Extract the desired fields
    $achieve = isset($form_data['achieve']) ? esc_html($form_data['achieve']) : 'N/A';
    $practice = isset($form_data['practice']) ? esc_html($form_data['practice']) : 'N/A';
    $challenge = isset($form_data['challenge']) ? esc_html($form_data['challenge']) : 'N/A';
    $comments = isset($form_data['comments']) ? esc_html($form_data['comments']) : 'N/A';

    // Display the extracted data
    echo "<h3>Submission ID: " . esc_html($submission->id) . "</h3>";
    echo "<p><strong>Achieve:</strong> $achieve</p>";
    echo "<p><strong>Practice:</strong> $practice</p>";
    echo "<p><strong>Challenge:</strong> $challenge</p>";
    echo "<p><strong>Comments:</strong> $comments</p>";
} else {
    echo "No OSOP submission found with the email $email.";
}
?>

<div>
<form method="post">
	<input type="hidden" name="action" value="write_osop_call_lesson_suggestions" />
	<input type="hidden" name="user_id" value="<?php echo $wp_uid; ?>" />
	<input type="hidden" name="email" value="<?php echo $contact['Email']; ?>" />
	<input type="hidden" name="keap_id" value="<?php echo $contact_id; ?>" />

<select name='dc_lesson'>
<option value=''>Choose a Lesson:</option>
<?php 
$lessons = $wpdb->get_results( "SELECT * FROM academy_lessons ORDER BY lesson_title ASC " );
foreach ($lessons as $lesson) {
    $lesson_title = stripslashes($lesson->lesson_title);  // Remove any backslashes from the title
    echo '<option value="'.esc_url(get_permalink($lesson->post_id)).'**'.esc_attr($lesson_title).'">'.esc_html($lesson_title).'</option>';
}
?>
</select>
<p></p>

	<select name='dc_course'>
<option value=''>Choose a Lesson Collection:</option>
<?php 
$courses = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_type = 'course' ORDER BY post_title ASC " );
//$courses = $wpdb->get_results( "SELECT * FROM academy_courses ORDER BY course_title ASC " );
foreach ($courses AS $course) {
	echo '<option value="'.get_permalink($course->ID).'**'.$course->post_title.'">'.$course->post_title.'</option>';
} ?>
</select>

<p></p>

	<select name='dc_premier_course'>
<option value=''>Choose a Premier Course:</option>
<?php 
$premier_courses = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_type = 'premier-course' ORDER BY post_title ASC " );
//$courses = $wpdb->get_results( "SELECT * FROM academy_courses ORDER BY course_title ASC " );
foreach ($premier_courses AS $premier_course) {
	echo '<option value="'.get_permalink($premier_course->ID).'**'.$premier_course->post_title.'">'.$premier_course->post_title.'</option>';
} ?>
</select>

<p></p>
<select name='dc_class'>
<option value=''>Choose a Class Replay:</option>
    <option value="/replays/?_class_type=jazz-piano**Jazz Piano Class">Jazz Piano Class</option>
    <option value="/replays/?_class_type=music-notation**Music Notation Class">Music Notation Class</option>
    <option value="/replays/?_class_type=pop-rock**Pop/Rock Class">Pop/Rock Class</option>
    <option value="/replays/?_class_type=practical-theory**Practical Music Theory Class">Practical Music Theory Class</option>
    <option value="/replays/?_class_type=music-history**Music History Class">Music History Class</option>
    <option value="/replays/?_class_type=transcription**Transcription Class">Transcription Class</option>
    <option value="/replays/?_class_type=improvisation**Improvisation Class">Improvisation Class</option>
    <option value="/replays/?_class_type=rhythm-training**Rhythm Class">Rhythm Class</option>
    <option value="/replays/?_class_type=vocal-training**Vocal Training Class">Vocal Training Class</option>
</select>
<br />
<p>Notes:</p>
<textarea name="osop_notes" cols="70" rows="3"></textarea>

<p> <button type="submit" class="button blue small">Save OSOP Suggestions</button> 
<input type="checkbox" name="back_to_osop" value="y" /> (check here to go back to OSOP)
</p>
</form>

<?php
// Initialize variables
$note = '';
$interest_level = 1; // Default value
$user_id = intval($wp_uid); // Make sure $wp_uid is defined before this
$osop_update = '';
// Check if a user ID is provided
if ($user_id) {
    // Query the academy_osop_followup table for the current user
    $result = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT note, interest_level FROM academy_osop_followup WHERE user_id = %d",
            $user_id
        ),
        ARRAY_A
    );

    // If data is found, populate variables
    if ($result) {
        $note = esc_textarea($result['note']);
        $interest_level = intval($result['interest_level']);
    }
}
?>

<h2>OSOP Followup:</h2>

<form action="" method="post">
    <input type="hidden" name="action" value="write_osop_followup" />
    <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>" />
    <input type="hidden" name="osop_update" value="<?php echo !empty($result) ? '1' : '0'; ?>" />
    <label for="note">Note:</label><br>
    <textarea id="note" name="note" rows="4" cols="50" required><?php echo $note; ?></textarea><br><br>

    <label for="interest_level">Interest Level:</label><br>
    <select id="interest_level" name="interest_level" required>
        <option value="1" <?php selected($interest_level, 1); ?>>1 - Low</option>
        <option value="2" <?php selected($interest_level, 2); ?>>2 - Medium</option>
        <option value="3" <?php selected($interest_level, 3); ?>>3 - High</option>
    </select><br><br>

    <input type="submit" value="Save Follow-Up" class="button blue small">
</form>
<a href="https://jazzedge.academy/willie/ja-admin/student.php?action=osop_ready&user_id=<?php echo $wp_uid; ?>&contact_id=<?php echo $contact_id; ?>"><button type="link" class="button red small">Mark OSOP Ready</button></a>
<?php 
$osop_ready = get_user_meta($wp_uid, 'osop_ready', true);
if ($osop_ready != 'yes') {
 ?>
<a href="https://jazzedge.academy/willie/ja-admin/student.php?action=osop_ready&user_id=<?php echo $wp_uid; ?>&contact_id=<?php echo $contact_id; ?>"><button type="link" class="button red small">Mark OSOP Ready</button></a>
</div>
<?php } ?>
<br />
<div style="border: 1px solid #000; padding: 20px; width: 40%; margin-left: 0px;">
<h3>Suggested Lessons:</h3><p></p>
<?php 
$suggestions = $wpdb->get_results( "SELECT * FROM academy_osop_suggestions WHERE user_id = '$wp_uid' " );

$query = $wpdb->prepare("SELECT notes FROM academy_osop_emailed WHERE user_id = %d LIMIT 1", $wp_uid );
$osop_notes = $wpdb->get_var($query); // get_var is used to get a single value


echo '<ol>';
foreach ($suggestions AS $suggestion) {
$err = (empty($suggestion->title) || empty($suggestion->url)) ? '<strong style="color:red">EMPTY</strong>' : '';
echo '<li><a href="?action=delete-osop-suggestion&id='.$suggestion->ID.'&contact_id='.$contact_id.'&user_id='.$wp_uid.'"><i class="fa-sharp fa-solid fa-trash"></i></a> '."<strong>$suggestion->title</strong> ($suggestion->url) | $suggestion->type $err</li>";
}
echo '</ol>';
echo "Notes: $osop_notes";
?>
</div>

<h2>Support Tickets:</h2>

<?php 
// Fetch tickets from wp_fs_tickets table for the specified customer ID, sorted by most recent
$tickets = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT customer_id, status, title, created_at, last_agent_response, id AS ticket_id
         FROM wp_fs_tickets
         WHERE customer_id = %d
         ORDER BY created_at DESC",
        $fluent_id
    )
);

// Display tickets in a table for the admin area
if (!empty($tickets)) {
    echo '<table border="1" cellpadding="10" cellspacing="0">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Customer ID</th>';
    echo '<th>Status</th>';
    echo '<th>Title</th>';
    echo '<th>Created At</th>';
    echo '<th>Last Agent Response</th>';
    echo '<th>Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($tickets as $ticket) {
        echo '<tr>';
        echo '<td>' . esc_html($ticket->customer_id) . '</td>';
        echo '<td>' . esc_html($ticket->status) . '</td>';
        echo '<td>' . esc_html($ticket->title) . '</td>';
        echo '<td>' . esc_html($ticket->created_at) . '</td>';
        echo '<td>' . esc_html($ticket->last_agent_response) . '</td>';
        echo '<td><a href="https://jazzedge.academy/wp-admin/admin.php?page=fluent-support#/tickets/' . esc_attr($ticket->ticket_id) . '/view" target="_blank">View Ticket</a></td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo 'No tickets found for this customer ID.';
}
 ?>

<?php 

$quizzes = $wpdb->get_results( "SELECT * FROM academy_premier_quiz_results WHERE user_id = $wp_uid ORDER BY date DESC" ); ?>
<h2>Premier Quiz Log:</h2>
<div class='rg-container hover-black' style='max-height: 200px; width: 500px; overflow: scroll;'>
	<table class='rg-table zebra' summary='Hed'>
		<thead>
			<tr>
				<th class='text'>Date Taken</th>
				<th class='text'>Score</th>
				<th class='text'>Class</th>
				<th class='text'>Action</th>
			</tr>
		</thead>
		<tbody>
<?php 

foreach ($quizzes AS $quiz) {
		$class_title = get_the_title($quiz->premier_class_id);
		echo "<tr>
		<td>".date('m-d-Y',strtotime($quiz->date))."</td>
		<td>$quiz->score</td>
		<td>$class_title</td>
		<td><a href='?action=delete_quiz&id=$quiz->ID&user_id=$wp_uid&contact_id=$contact_id' onclick=\"return confirm('Are you sure you want to delete their quiz entry?')\" ><i class='fa-sharp fa-solid fa-trash'></i> Delete</a></td>
		</tr>";
}
 ?>
		</tbody>
	</table>
</div>

<h2>Promo Credit Log:</h2>
<div class='rg-container hover-black'>
	<table class='rg-table zebra' summary='Hed'>
		<thead>
			<tr>
				<th class='text'>Date Added</th>
				<th class='text'># Credits</th>
				<th class='text'>Note</th>
				<th class='text'>IP</th>
			</tr>
		</thead>
		<tbody>
<?php 

foreach ($promo_credits AS $promo_credit) {
		echo "<tr>
		<td>".date('m-d-Y',strtotime($promo_credit->date))."</td>
		<td>$promo_credit->credits_received</td>
		<td>$promo_credit->note</td>
		<td>$promo_credit->ip</td>
		</tr>";
}
 ?>
		</tbody>
	</table>
</div>
 
<h2>Academy Credit Log:</h2>
<div class='rg-container hover-black'>
	<table class='rg-table zebra' summary='Hed'>
		<thead>
			<tr>
				<th class='text'>Date Used</th>
				<th class='text'>Event ID</th>
				<th class='text'>Type</th>
				<th class='text'>Title</th>
				<th class='text'>Action</th>
			</tr>
		</thead>
		<tbody>
<?php 

foreach ($credits_used AS $credit_used) {
		$title = get_the_title($credit_used->post_id);
		$title_link = get_the_permalink($credit_used->post_id);
		$event_date = get_post_meta( $credit_used->post_id, 'event_date', true );
		//$event_checkin = $wpdb->get_var( "SELECT datetime FROM academy_zoom_checkin WHERE user_id = $wp_uid && event_id = $credit_used->post_id " );
		echo "<tr>
		<td>".date('m-d-Y',$credit_used->timestamp)."</td>
		<td>$credit_used->post_id</td>
		<td>$credit_used->type</td>
		<td><a href='$title_link' target='_blank'>$title</a></td>
		<td><a href='?action=delete_credit_purchase&post_id=$credit_used->post_id&user_id=$wp_uid&contact_id=".$contact['Id']."''><i class='fa-sharp fa-solid fa-trash''></i> delete</a></td>
		</tr>";
}
 ?>
		</tbody>
	</table>
</div>


<form method="post">
	<input type="hidden" name="action" value="save_credit_purchase_lesson" />
	<input type="hidden" name="user_id" value="<?php echo $wp_uid; ?>" />
	<input type="hidden" name="email" value="<?php echo $contact['Email']; ?>" />
	<input type="hidden" name="keap_id" value="<?php echo $contact_id; ?>" />

<select name='credit_lesson'>
<option value=''>Choose a Lesson:</option>
<?php 
$lessons = $wpdb->get_results( "SELECT * FROM academy_lessons ORDER BY lesson_title ASC " );
foreach ($lessons as $lesson) {
    $lesson_title = stripslashes($lesson->lesson_title);  // Remove any backslashes from the title
    echo '<option value="'.$lesson->post_id.'">'.esc_html($lesson_title).'</option>';
}
?>
</select>
<p> <button type="submit" class="button blue small">Save Credit Lesson</button> 
</p>
</form>


<hr>
<h2>Grant Access to Premier Course:</h2>
	<form method="post">
	<input type="hidden" name="action" value="grant_access_premier_course" />
	<input type="hidden" name="user_id" value="<?php echo $wp_uid; ?>" />
	<input type="hidden" name="email" value="<?php echo $contact['Email']; ?>" />
	<select name="premier_course_sku">
	<option value="">Choose Premier Course...</option>
<?php 
	foreach ($premier_courses AS $premier_course) {
		$premier_course_sku = get_field( "premier_course_sku", $premier_course->ID );
		echo "<option value='$premier_course_sku'>($premier_course->ID) $premier_course->post_title</option>";
	}
 ?>
 	</select>
 <button type="submit" class="button blue small">Grant Access</button>
</form>

<h2>Purchased Events</h2>
	<div style="height: 200px; overflow: scroll;">
	<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
	<thead>
	<tr>
		<th>ID</th>
		<th>Event SKU</th>
		<th>Date Purchased</th>
		<th>Edit</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
		<?php 
		$eventpayments = $wpdb->get_results( "SELECT * FROM academy_event_purchased WHERE user_id = $wp_uid " );
		foreach ($eventpayments AS $eventpayment) {
			echo "<tr>
			<td>$eventpayment->ID</td>
			<td>$eventpayment->event_sku</td>
			<td>$eventpayment->date_purchased</td>
			<td><a href='edit_event_purchased.php?id=$eventpayment->ID'><i class='fa-sharp fa-solid fa-pencil'></i></a></td>
			<td><a href='actions.php?action=delete-premier-course-access&event_id=$eventpayment->ID&contact_id=".$contact['Id']."'  onclick=\"return confirm('Really? Delete Access to this Premier Course?')\"><i class=\"fa-sharp fa-solid fa-trash\"></i></a></td>
			</tr>";
		}
		?>
	</tbody>				
</table>
</div>
	


<?php 
/*
	10120	Delete FASTERFINGERS Bundle Access
	9901	Delete Jazz Improv Bundle Access	
	9899	Delete Technique Bundle Access	
	9897	Delete Blues Licks Bundle Access	
	9895	Delete Jazz Standards Bundle Access
	9919 	Delete SAML Bundle Access
	*/
 ?>

<div>
<select id="dynamic_select">
  <option value="" selected>Grant Access to Bundle...</option>
  <option value="actions.php?action=add-bundle-access&contact_id=<?php echo $contact_id; ?>&bundle_id=10">Add Faster Fingers Bundle</option>
  <option value="actions.php?action=add-bundle-access&contact_id=<?php echo $contact_id; ?>&bundle_id=4">Add Jazz Standards Bundle</option>
  <option value="actions.php?action=add-bundle-access&contact_id=<?php echo $contact_id; ?>&bundle_id=1">Add Jazz Improv Bundle</option>
  <option value="actions.php?action=add-bundle-access&contact_id=<?php echo $contact_id; ?>&bundle_id=2">Add Blues Licks Bundle</option>
  <option value="actions.php?action=add-bundle-access&contact_id=<?php echo $contact_id; ?>&bundle_id=3">Add Technique Bundle</option>
  <option value="actions.php?action=add-bundle-access&contact_id=<?php echo $contact_id; ?>&bundle_id=5">Add SAML Bundle</option>
</select>



<select id="dynamic_select">
  <option value="" selected>Remove Access to Bundle...</option>
  <option value="actions.php?action=remove-bundle-access&contact_id=<?php echo $contact_id; ?>&tag_id=10120">Remove Faster Fingers Bundle</option>
  <option value="actions.php?action=remove-bundle-access&contact_id=<?php echo $contact_id; ?>&tag_id=9895">Remove Jazz Standards Bundle</option>
  <option value="actions.php?action=remove-bundle-access&contact_id=<?php echo $contact_id; ?>&tag_id=9901">Remove Jazz Improv Bundle</option>
  <option value="actions.php?action=remove-bundle-access&contact_id=<?php echo $contact_id; ?>&tag_id=9897">Remove Blues Licks Bundle</option>
  <option value="actions.php?action=remove-bundle-access&contact_id=<?php echo $contact_id; ?>&tag_id=9899">Remove Technique Bundle</option>
  <option value="actions.php?action=remove-bundle-access&contact_id=<?php echo $contact_id; ?>&tag_id=9919">Remove SAML Bundle</option>
</select>

<script>
    jQuery(function(){
      // bind change event to select
      jQuery('#dynamic_select').on('change', function () {
          var url = jQuery(this).val(); // get selected value
          if (url) { // require a URL
              window.location = url; // redirect
          }
          return false;
      });
    });
</script>
</div><br />

Promo Codes Used: <?php echo $contact['_PromoCodesUsed']; ?>


</p>
<h2>Link Views</h2>
<ul>
<li>Past 30 Days: <?php echo get_user_link_views($wp_uid,30);  ?></li>
<li>All Time: <?php echo get_user_link_views($wp_uid);  ?></li>
</ul>

<h2>Video Views</h2>
<ul>
<li>Past 30 Days: <?php echo get_user_video_views($wp_uid,30);  ?></li>
<li>All Time: <?php echo get_user_video_views($wp_uid);  ?></li>
</ul>

<div style="clear:both;">
<h2>Credit Cards</h2>
	<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
	<thead>
	<tr>
		<th>Id</th>
		<th>Card Type</th>
		<th>Expiration</th>
		<th>Status</th>
	</tr>
	</thead>
	<tbody>
		<?php 
		foreach ($credit_cards AS $credit_card) {
			echo "<tr>
			<td>$credit_card[Id]</td>
			<td>$credit_card[CardType]</td>
			<td>$credit_card[ExpirationMonth]/$credit_card[ExpirationYear]</td>
			<td>".card_status($credit_card['Status'])."</td>
			</tr>";
		}
		?>
	</tbody>				
</table>

<h2>Payments</h2>
<?php if (in_array(9223,$tag_ids_array)) { ?>
	<div style="background-color: red; color: white; width: 300px; padding: 10px; margin-bottom: 5px;">They have received a Jazzedge Academy refund.</div>
<?php } ?>
<?php if (in_array(9757,$tag_ids_array)) { ?>
	<div style="background-color: green; color: white; width: 300px; padding: 10px; margin-bottom: 5px;">Received a free month of Jazzedge Academy.</div>
<?php } ?>
<?php if (in_array(9753,$tag_ids_array)) { ?>
	<div style="background-color: red; color: white; width: 300px; padding: 10px;">They have received a HomeSchoolPiano refund.</div>
<?php } ?>

	<div style="height: 300px; overflow: scroll;">
	<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
	<thead>
	<tr>
		<th>Id</th>
		<th>PayAmt</th>
		<th>PayDate</th>
		<th>LastUpdated</th>
		<th>PayType</th>
		<th>ChargeId</th>
		<th>RefundId</th>
	</tr>
	</thead>
	<tbody>
		<?php 
		krsort($payments);
		foreach ($payments AS $payment) {
			echo "<tr>
			<td>$payment[Id]</td>
			";
// <td><a href='https://ft217.infusionsoft.com/Job/manageJob.jsp?view=edit&ID=$payment[InvoiceId]' target='_blank'>$payment[InvoiceId] <i class='fa-sharp fa-solid fa-arrow-up-right-from-square'></i></a></td>
			echo "<td>$$payment[PayAmt]</td>
			<td>".convert_infusion_date($payment['PayDate'])."</td>
			<td>".convert_infusion_date($payment['LastUpdated'])."</td>
			<td>$payment[PayType]</td>
			<td>$payment[ChargeId]</td>
			<td>$payment[RefundId]</td>
			</tr>";
		}
		?>
	</tbody>				
</table>
	</div>
</div>


<?php 
$upgrade_downgrade = keap_get_contact_fields($contact_id,array('_AcademyUpgradeDowngradeDate','_AcademyUpgradeDowngradeProduct','_AcademyUpgradeDowngradeDetails')); 
//echo '<pre><h2>$upgrade_downgrade</h2>'; print_r($upgrade_downgrade); echo '</pre>';
if (!empty($upgrade_downgrade)) {
	echo '<h2>Upgrade/Downgrade</h2><ul>';
	echo '<li>Upgrade/Downgrade Date: <strong>'.convert_infusionsoft_date($upgrade_downgrade['_AcademyUpgradeDowngradeDate']).'</strong></li>';
	echo '<li>Product: '.$upgrade_downgrade['_AcademyUpgradeDowngradeProduct'].'</li>';
	echo '<li>Details: '.$upgrade_downgrade['_AcademyUpgradeDowngradeDetails'].'</li>';
	echo '</ul>';
}
?>

<h2>Subscriptions</h2>
<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
	<thead>
		<tr>
		<th>Id</th>
		<th>Status</th>
		<th>Product</th>
		<th>Auto</th>
		<th>Billing Amt</th>
		<th>Next Bill Date</th>
		<th>Plan ID</th>
		<th>Reason Stopped</th>
		<th>Gateway</th>
		<th>Proration</th>
		<th>Originating OrderId</th>
		<th></th>
		</tr>
	</thead>
	<tbody>
<?php 
$has_active_subscription = FALSE;
foreach ($subscriptions AS $subscription) {

// proration
$now = time(); 
$your_date = strtotime($subscription['PaidThruDate']);
$datediff = $now - $your_date;
$proration_days = round($datediff / (60 * 60 * 24));
$proration = round($subscription['BillingAmt'] - (($subscription['BillingAmt'] / 365) * $proration_days),2);

	$returnFields = array( 'ProductName', 'ProductPrice', 'Sku');
	$pid = $subscription['ProductId'];
	$query = array('Id' => $pid);
	$product = $app->dsQuery("Product",1,0,$query,$returnFields);
	$product_name = $product[0]['ProductName'];
	if ($subscription['AutoCharge'] == 1) { $has_active_subscription = TRUE; }
	$autocharge = ($subscription['AutoCharge'] == 1) ? '<strong style="color:green">YES</strong>' : '<strong style="color:red">NO</strong>';
	echo "<tr><td>$subscription[Id] <a href='https://ft217.infusionsoft.com/JobRecurring/manageJobRecurring.jsp?view=edit&ID=$subscription[Id]' target='_blank'><i class='fa-sharp fa-solid fa-arrow-up-right-from-square'></i></a></td><td>$subscription[Status] </td>
	<td><a href='https://ft217.infusionsoft.com/JobRecurring/manageJobRecurring.jsp?view=edit&ID=$subscription[Id]' target='_blank'>$product_name</a></td><td>$autocharge</td>
	<td>$$subscription[BillingAmt]</td>
	<td>".convert_infusion_date($subscription['NextBillDate'])."</td>
	<td>$subscription[SubscriptionPlanId]</td>
	<td>$subscription[ReasonStopped]</td>
	<td>".convert_gateway($subscription['PaymentGatewayId'])."</td>
	<td>";
	if ($proration_days < 364) {
	echo "$$proration";
	}
	echo "</td>
	<td>$subscription[OriginatingOrderId] <a href='https://ft217.infusionsoft.com/Job/manageJob.jsp?view=edit&ID=$subscription[OriginatingOrderId]' target='_blank'><i class='fa-sharp fa-solid fa-arrow-up-right-from-square'></i></a></td>
	<td><a href='actions.php?action=delete-subscription&id=$subscription[Id]&contact_id=$contact_id'  onclick=\"return confirm('Really? Delete Subscription ID: $subscription[Id]')\"><i class=\"fa-sharp fa-solid fa-trash\"></i></a></td></tr>";
}
?>
	</tbody>				
</table>
<?php if (!in_array(9757,$tag_ids_array)) { ?>
<p><a href="actions.php?action=academy-free-month-note&contact_id=<?php echo $contact_id; ?>" onclick="return confirm('Are you sure you want to give them a $20 credit?')" class="medium button green">(Add Note) Academy $20 Courtesy Credit</a></p>
<?php } else { ?>
<p>They have already received a free month of access to Jazzedge Academy.</p>
<?php } ?>

<h2>Paid Invoices</h2>
	<div style="height: 300px; overflow: scroll;">
	<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
	<thead>
		<tr>
		<th>Date</th>
		<th>Order ID</th>
		<th>PayPlanStatus</th>
		<th>Product</th>
		<th>Paid</th>
		<th>Amount</th>
		<th></th>
		</tr>
	</thead>
	<tbody>	
<?php	
krsort($paid_invoices);
// Function to safely get a value from an array
function safe_get($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
}

foreach ($paid_invoices AS $invoice) {
	$returnFields = array('Id', 'ProductName', 'Sku');
	$productDetails = $app->dsLoad("Product", $invoice['ProductSold'], $returnFields);
	$paid = ($invoice['PayStatus'] == 1) ? 'Paid' : '<strong style="color:red;">NO</strong>';


// Inside your existing code
echo "<tr>";
echo "<td>" . (isset($invoice['DateCreated']) ? convert_infusion_date($invoice['DateCreated']) : 'N/A') . "</td>";
echo "<td><a href='https://ft217.infusionsoft.com/Job/manageJob.jsp?view=edit&ID=" . safe_get($invoice, 'Id') . "' target='_blank'>" . safe_get($invoice, 'Id') . "</a></td>";
echo "<td>" . safe_get($invoice, 'PayPlanStatus') . "</td>";
echo "<td>" . safe_get($productDetails, 'ProductName') . "</td>";
echo "<td>$paid</td>";
echo "<td>$" . safe_get($invoice, 'TotalDue') . "</td>";
echo "</tr>";
	//echo "<tr><td>".convert_infusion_date($invoice['DateCreated'])."</td><td><a href='https://ft217.infusionsoft.com/Job/manageJob.jsp?view=edit&ID=$invoice[Id]' target='_blank'>$invoice[Id]</a></td><td>$invoice[PayPlanStatus]</td><td>$productDetails[ProductName]</td><td>$paid</td><td>$$invoice[TotalDue]</td>";
	echo "<td><a href='?action=delete&id=$invoice[Id]&contact_id=$contact_id&key=hjkl345hjdfyer79345kjhh' onclick=\"return confirm('Really?')\">".'<i class="fa-sharp fa-solid fa-trash"></i></a></td></tr>';
	}
	?>
	</tbody>				
</table>
	</div>

<h2>Failed Invoices</h2>
	<table cellpadding="0" cellspacing="0" width="100%" class="sortable">
	<thead>
		<tr>
			<th>Date</th>
			<th>Order ID</th>
			<th>Product</th>
			<th>Paid</th>
			<th>Amount</th>
			<th>Delete</th>
		</tr>
	</thead>
	<tbody>	

<?php
$failed_invoices_count = 0;
foreach ($failed_invoices AS $invoice) {
	$failed_invoices_count ++;
	$returnFields = array('Id', 'ProductName', 'Sku');
	$productDetails = $app->dsLoad("Product", $invoice['ProductSold'], $returnFields);
	$paid = ($invoice['PayStatus'] == 1) ? 'Paid' : '<strong style="color:red;">NO</strong>';

if (is_array($invoice) && isset($invoice['DateCreated'], $invoice['Id'], $invoice['TotalDue']) && is_array($productDetails) && isset($productDetails['ProductName'])) {
    echo "<tr><td>" . convert_infusion_date($invoice['DateCreated']) . "</td><td>{$invoice['Id']}</td><td>{$productDetails['ProductName']}</td><td>$paid</td><td>{$invoice['TotalDue']}</td>";
} else {
    // Handle the error or log an appropriate message here
   // error_log('Invalid data format for invoice or product details.');
}

//	echo "<tr><td>".convert_infusion_date($invoice['DateCreated'])."</td><td>$invoice[Id]</td><td>$productDetails[ProductName]</td><td>$paid</td><td>$invoice[TotalDue]</td>";
	echo "<td><a href='actions.php?action=delete&id=$invoice[Id]&contact_id=$contact_id' onclick=\"return confirm('Really?')\">".'<i class="fa-sharp fa-solid fa-trash"></i></a></td></tr>';
	echo "</tr>";
}
?>
	</tbody>				
</table>
			
<a href="actions.php?action=delete-all-failed-invoices&contact_id=<?php echo $contact_id; ?>" class="medium button red" onclick="return confirm('Really?')">Delete All Failed Invoices</a>

<h2>Do They Have Access?</h2>
					
<table cellpadding="0" cellspacing="0" width="100%" class="sortable">

	<thead>
		<tr>
			<th>ACADEMY</th>
			<th>JE</th>
			<th>NBP</th>
			<th>PWW</th>
			<th>HSP</th>
			<th>CPL</th>
			<th>RPL</th>
			<th>FPL</th>
			<th>PBP</th>
			<th>JPT</th>
			<th>MTO</th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td width="10%"><?php echo $academy_access; ?></td>
			<td width="10%"><?php echo $je_access; ?> <br><br>(<a href="actions.php?action=give-je-access&contact_id=<?php echo $contact_id; ?>">add</a> | <a href="actions.php?action=remove-je-access&contact_id=<?php echo $contact_id; ?>">remove</a>)</td>
			<td width="10%"><?php //echo $nbp_access; ?>
			<?php 
			$no_expiration_date = (strtotime($expiration_date) < 946684800) ? TRUE : FALSE;
			$is_expired = (strtotime($je_exp_date) < time()) ? TRUE : FALSE;
			$expired = ( $no_expiration_date == TRUE ) ? FALSE : $is_expired;
			//$is_expired = (strtotime($je_exp_date) < time()) ? TRUE : FALSE;
			if ( $expired == TRUE ) { echo 'expired'; } else { echo $nbp_access; }
			?>
			</td>
			<td width="10%"><?php echo $pww_access; ?> <br><br>(<a href="actions.php?action=give-pww-access&contact_id=<?php echo $contact_id; ?>">add</a> | <a href="actions.php?action=remove-pww-access&contact_id=<?php echo $contact_id; ?>">remove</a>)</td>
			<td width="10%"><?php echo $hsp_access; ?> <br><br>(add | <a href="actions.php?action=remove-hsp-access&contact_id=<?php echo $contact_id; ?>">remove</a>)</td>
			<td width="10%"><?php echo $cpl_access; ?></td>
			<td width="10%"><?php echo $rpl_access; ?></td>
			<td width="10%"><?php echo $fpl_access; ?></td>
			<td width="10%"><?php echo $pbp_access; ?></td>
			<td width="10%"><?php echo $jpt_access; ?></td>
			<td width="10%"><?php echo $mto_access; ?></td>
		</tr>
		<tr>
			<td><?php echo $academy_last_login; ?></td>
			<td><?php echo $je_last_login; ?></td>
			<td><?php echo $je_last_login; ?></td>
			<td><?php echo $pww_last_login; ?></td>
			<td><?php echo $hsp_last_login; ?></td>
			<td><?php echo $cpl_last_login; ?></td>
			<td><?php echo $rpl_last_login; ?></td>
			<td><?php echo $fpl_last_login; ?></td>
			<td><?php echo $pbp_last_login; ?></td>
			<td><?php echo $jpt_last_login; ?></td>
			<td><?php echo $mto_last_login; ?></td>
		</tr>

	</tbody>

</table>


<div id="wrapper">
    <div id="one">
    <h2>Contact Tags</h2>

<ul>

<?php foreach ($tags AS $tag) {
	$tags_to_mark = array('ACADEMY_SONG','ACADEMY_ACADEMY','ACADEMY_PREMIER','JE_MONTHLY','****PAYMENT FAILED****','NO ACTIVE SUBSCRIPTION','PWW_STUDIO_','JE_YEAR','JE_MONTHLY_14DAY','JE_YEARLY','JE_YEARLY_14DAY','NBP_MONTHLY_14DAY','NBP_MONTHLY','NBP_YEARLY_14DAY','NBP_YEARLY','JE_CANCELLED','PWW_STUDIO_CANC','PayPal Billing Cancelled','ACADEMY_FREE');
	if (in_array($tag['ContactGroup'],$tags_to_mark)) { 
?>
		<li><a href="actions.php?action=remove-tag&contact_id=<?php echo $contact_id;?>&tag_id=<?php echo $tag['GroupId'];?>" onclick="return confirm('Delete Tag - <?php echo $tag['ContactGroup']; ?>?')"><i class="fa-sharp fa-solid fa-trash"></i></a> (<?php echo $tag['GroupId']; ?>) <strong style="color: #C00;"><?php echo $tag['ContactGroup']; ?></strong> (<?php echo convert_infusion_date($tag['DateCreated']); ?>)</li>

<?php } else { ?>
		<li><a href="actions.php?action=remove-tag&contact_id=<?php echo $contact_id;?>&tag_id=<?php echo $tag['GroupId'];?>" onclick="return confirm('Delete Tag - <?php echo $tag['ContactGroup']; ?>?')"><i class="fa-sharp fa-solid fa-trash"></i></a> (<?php echo $tag['GroupId']; ?>) <?php echo $tag['ContactGroup']; ?> (<?php echo convert_infusion_date($tag['DateCreated']); ?>)</li>
<?php 	} ?>
<?php } ?>
</ul>
<br /><br /><br />
    </div>
    <div id="two">
    <h2>Actions</h2>
<?php
	// 8713	**First Payment Failure**
	$memberium_failed_payment_campaign = (array_intersect(array(8713),$tag_ids_array)) ? TRUE : FALSE;
?>
<?php
if (isset($_GET['debug'])) { 
	echo "memberium_failed_payment_campaign = $memberium_failed_payment_campaign<br />
	opt_status = $opt_status<br />
	failed_invoices_count = $failed_invoices_count";
} 

$academy_memberships = array(
	"9661" => "ACADEMY_FREE",
    "9659" => "ACADEMY_PREMIER",
    "9657" => "ACADEMY_ACADEMY",
    "9653" => "ACADEMY_SONG",
    "9827" => "JA_MONTHLY_LSN_ONLY",
    "9825" => "JA_MONTHLY_LSN_COACHING",
    "9823" => "JA_MONTHLY_LSN_CLASSES",
    "9821" => "JA_MONTHLY_PREMIER",
    "9819" => "JA_YEAR_LSN_ONLY",
    "9817" => "JA_YEAR_LSN_COACHING",
    "9815" => "JA_YEAR_LSN_CLASSES",
    "9813" => "JA_YEAR_PREMIER"
);

?>

<h2>Give Access to Membership Level</h2>
<form method="post" action="actions.php">
	<input type="hidden" name="action" value="add_tag" />
	<input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
	<select name="tag_id">
		<option value="">Choose a membership...</option>
		<?php 
		foreach ($academy_memberships AS $ja_tag_id => $ja_membership) {
			echo "<option value=\"$ja_tag_id\">$ja_membership</option>\n";
		}
		?>
	</select>
	<button type="submit" class="button blue small">Grant Access</button>
</form>
<br /><br />

<p><a href="actions.php?action=first-payment-failure&contact_id=<?php echo $contact_id; ?>" class="medium button green" onclick="return confirm('Really?')">(Add to) MEMBERIUM - Failed Payment</a></p>
<?php if ($memberium_failed_payment_campaign == FALSE && $opt_status == '' && $failed_invoices_count > 0 && $failed_invoices_count < 15) { ?>
<p><a href="actions.php?action=first-payment-failure&contact_id=<?php echo $contact_id; ?>" class="medium button green" onclick="return confirm('Really?')">(Add to) MEMBERIUM - Failed Payment</a></p>
<?php } elseif ($memberium_failed_payment_campaign == FALSE && $opt_status != 'Opt-in') { ?>
<div class="message errormsg"><p>They are opt-out so they will not get emails</p></div>
<?php } elseif ($failed_invoices_count >= 1 && $failed_invoices_count < 4) { ?>
<p><a href="actions.php?action=payment-failure-restart&contact_id=<?php echo $contact_id; ?>" class="medium button red" onclick="return confirm('Really?')">(RESTART) MEMBERIUM - Failed Payment</a></p>
<?php } ?>

<?php if ($failed_invoices_count >= 4) { ?>
<div class="message errormsg"><p>They have too many failed payments to bother sending a Failed Payment email.</p></div>
<?php } ?>
<?php if ($has_active_subscription) { ?>
<p>
<a href="actions.php?action=tag-failed-payment&contact_id=<?php echo $contact_id; ?>" class="medium button red">Apply Failed Payment Tag</a>
</p>
<p>
<a href="actions.php?action=cancel-all-subs&contact_id=<?php echo $contact_id; ?>" class="medium button red" onclick="return confirm('Really?')">Cancel All Subscriptions</a>
</p>
<p>
<a href="actions.php?action=payment-successfully-applied&contact_id=<?php echo $contact_id; ?>" class="medium button green" onclick="return confirm('Really?')">Payment Applied (Send Email)</a>
</p>

<p>
<a href="actions.php?action=payment-successfully-applied-no-email&contact_id=<?php echo $contact_id; ?>" class="medium button green" onclick="return confirm('Really?')">Payment Applied (NO Email)</a>
</p>

<?php } ?>
<p>
<a href="actions.php?action=payment-failed-talk-nbp&contact_id=<?php echo $contact_id; ?>" class="medium button green" onclick="return confirm('Really?')">Send Email (CC Update and NBP)</a>
</p>


    <h2>Contact Notes</h2>
    	<p>
    	Be sure to add the date (MM/DD/YYYY) before the note.
    	</p>
		<form action="actions.php" method="post">
			<p>
				<textarea rows="10" cols="103" name="contact_notes"><?php echo $contact_notes; ?></textarea>
			</p>
			<p>
				<input type="submit" class="submit" value="Update Notes" />
				<input type="hidden" name="action" value="update-notes" />
				<input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
			</p>
		</form>
    </div>
</div>




	
	<?php } ?>
</div>	


<?php include_once('includes/scripts.php'); ?>
<?php include_once('includes/footer.php'); ?>

