<?php
ob_start();
include('header.php');

$SCHOOL  = $_GET['SCHOOL'];
$COUNTRY = $_GET['COUNTRY'];
$REGION  = $_GET['REGION'];
$STATUS  = $_GET['STATUS'];
$PROMO_ID = $_GET['PROMO_ID']; // Promotion to update

// Get country, school & currency info
$details_user = mysqli_fetch_array(mysqli_query($conn, "
    SELECT * FROM schools 
    LEFT JOIN countries ON schools.country_ref = countries.id 
    WHERE school_id='$SCHOOL'
"));

$Country_currency_code = $details_user['Country_currency_code'];
$currency_usd          = $details_user['currency_usd']; // local needed to buy 1 USD

// ✅ Load current promotion record
$promo = mysqli_fetch_array(mysqli_query($conn,"
    SELECT * FROM students_promotion WHERE promotion_id='$PROMO_ID'
"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Promotion</title>
</head>
<body class="h-screen font-sans login bg-cover">

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
            <div class="leading-loose">

                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                    <p class="text-gray-800 font-medium">
                        Update Promotion <strong><?php echo $promo['promotion_name']." / ".$Country_currency_code; ?></strong>
                    </p>

<?php
// ✅ UPDATE LOGIC
if (isset($_POST['Update'])) {

    $promotion_name         = mysqli_real_escape_string($conn, $_POST['promotion_name']);
    $promotion_certification= $_POST['promotion_certification'];
    $promotion_pay_local    = $_POST['promotion_pay_local'];
    $promotion_year         = $_POST['promotion_year'];
    $promotion_region       = $_POST['promotion_region'];
    $promotion_country      = $_POST['promotion_country'];
    $promotion_school       = $_POST['promotion_school'];
    $promotion_status       = $_POST['promotion_status'];
    $promotion_payment      = $_POST['promotion_payment'];

    // Calculate dates
    $cert_query = mysqli_query($conn,"SELECT * FROM certifications WHERE certification_id='$promotion_certification'");
    $cert = mysqli_fetch_array($cert_query);
    $duration = $cert['certification_duration'];

    $promotion_from = date('Y-m-d', strtotime($_POST['promotion_from']));

    // First payment date = half duration
    $date = new DateTime($promotion_from);
    $date->modify("+" . ($duration/2) . " months");
    $promotion_fp_date = $date->format('Y-m-d');

    // End date
    $date = new DateTime($promotion_from);
    $date->modify("+" . $duration . " months");
    $promotion_to = $date->format('Y-m-d');

    // ✅ Auto-calculate USD from local
    $promotion_pay_usd = 0;
    if ($currency_usd > 0 && $promotion_pay_local > 0) {
        $promotion_pay_usd = $promotion_pay_local / $currency_usd;
    }

    // ✅ UPDATE QUERY
    $update = mysqli_query($conn, "
        UPDATE students_promotion SET
            promotion_name='$promotion_name',
            promotion_certification='$promotion_certification',
            promotion_pay_usd='$promotion_pay_usd',
            promotion_pay_local='$promotion_pay_local',
            promotion_from='$promotion_from',
            promotion_fp_date='$promotion_fp_date',
            promotion_to='$promotion_to',
            promotion_year='$promotion_year',
            promotion_region='$promotion_region',
            promotion_country='$promotion_country',
            promotion_school='$promotion_school',
            promotion_status='$promotion_status',
            promotion_payment='$promotion_payment'
        WHERE promotion_id='$PROMO_ID'
    ");

    if ($update) {
        header('location:Schools_Payments?SCHOOL='.$SCHOOL.'&COUNTRY='.$COUNTRY.'&REGION='.$REGION.'&STATUS='.$promotion_status);
        exit;
    } else {
        echo '<div class="bg-red-300 mb-2 border border-red-300 text-red-dark px-4 py-3 rounded">
                <strong>Error!</strong> Update failed.
              </div>';
    }
}
?>

                    <!-- UPDATE FORM -->

                    <label class="block text-xs mb-1">Certification</label>
                    <select name="promotion_certification" class="w-full bg-gray-200 border rounded py-3 px-4" required>
                        <option value="">-- Select Certification --</option>
                        <?php
                        $certs = mysqli_query($conn,"SELECT * FROM certifications WHERE certification_status='Active'");
                        while($c = mysqli_fetch_array($certs)){
                            $selected = ($promo['promotion_certification'] == $c['certification_id']) ? 'selected' : '';
                            echo "<option value='{$c['certification_id']}' $selected>{$c['certification_name']}</option>";
                        }
                        ?>
                    </select>

                    <label class="block text-sm text-gray-600">Promotion Name</label>
                    <input class="w-full px-5 py-1 bg-gray-200 rounded" name="promotion_name" type="text" value="<?php echo $promo['promotion_name']; ?>" required>

                    <input type="hidden" name="promotion_school" value="<?php echo $SCHOOL; ?>">
                    <input type="hidden" name="promotion_country" value="<?php echo $COUNTRY; ?>">
                    <input type="hidden" name="promotion_region" value="<?php echo $REGION; ?>">

                    <label class="block text-sm text-gray-600 mt-2">
                        Amount in <?php echo $Country_currency_code; ?>
                    </label>
                    <input class="w-full px-5 py-1 bg-gray-200 rounded" name="promotion_pay_local" type="number" step="0.01" min="0" value="<?php echo $promo['promotion_pay_local']; ?>" required>

                    <label class="block text-sm text-gray-600 mt-2">Year</label>
                    <input class="w-full px-5 py-1 bg-gray-200 rounded" name="promotion_year" type="number" value="<?php echo $promo['promotion_year']; ?>" required>

                    <label class="block text-sm text-gray-600 mt-2">From</label>
                    <input class="w-full px-5 py-1 bg-gray-200 rounded" name="promotion_from" type="date" value="<?php echo $promo['promotion_from']; ?>" required>

                    <label class="block text-sm text-gray-600 mt-2">Payment Settings</label>
                    <select name="promotion_payment" class="w-full bg-gray-200 border rounded py-3 px-4" required>
                        <option value="">Select</option>
                        <option value="Enable"  <?php if($promo['promotion_payment']=="Enable") echo "selected"; ?>>Enable</option>
                        <option value="Desable" <?php if($promo['promotion_payment']=="Desable") echo "selected"; ?>>Desable</option>
                    </select>

                    <label class="block text-sm text-gray-600 mt-2">Status</label>
                    <select name="promotion_status" class="w-full bg-gray-200 border rounded py-3 px-4" required>
                        <option value="">Select</option>
                        <option value="Active"   <?php if($promo['promotion_status']=="Active") echo "selected"; ?>>Active</option>
                        <option value="Inactive" <?php if($promo['promotion_status']=="Inactive") echo "selected"; ?>>Inactive</option>
                    </select>

                    <div class="mt-4">
                        <button type="submit" name="Update" class="px-4 py-1 bg-blue-500 text-white rounded">
                            Update Promotion
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php ob_end_flush(); ?>
