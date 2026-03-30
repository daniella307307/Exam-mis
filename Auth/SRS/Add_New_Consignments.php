<?php
ob_start();
include('header.php');

// Handle form submission
if(isset($_POST['Update'])){
    // Start transaction for atomic operation
    $conn->begin_transaction();
    
    try {
        // Get the last consignment_id from database WITH LOCK
        $sql = "SELECT consignment_id FROM Equipment_consignment ORDER BY consignment_id DESC LIMIT 1 FOR UPDATE";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $last_id = $row['consignment_id'];
        } else {
            $last_id = 0;
        }
        
        $new_consignment_id = $last_id + 1;
        $currentYear = date('Y');
        $consignment_number = "BLIS/CONS/" . $currentYear . "/" . str_pad($new_consignment_id, 5, '0', STR_PAD_LEFT);
        
        $consignment_origine  = $_POST['consignment_origine'];
        $consignment_purchase  = $_POST['consignment_purchase'];
        $consignment_shipping  = $_POST['consignment_shipping'];
        $consignment_bank_transfer  = $_POST['consignment_bank_transfer'];
        $consignment_transport  = $_POST['consignment_transport'];
        $consignment_taxes  = $_POST['consignment_taxes'];
        $consignment_total  = $_POST['consignment_total'];
        $consignment_apliedpercent  = $_POST['consignment_apliedpercent'];
        
        // Handle file upload
        $consignment_document = '';
        if(isset($_FILES['consignment_document']) && $_FILES['consignment_document']['error'] == 0) {
            $target_dir = "uploads/";
            if(!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_name = time() . '_' . basename($_FILES["consignment_document"]["name"]);
            $target_file = $target_dir . $file_name;
            if(move_uploaded_file($_FILES["consignment_document"]["tmp_name"], $target_file)) {
                $consignment_document = $target_file;
            }
        }
        
        $consignment_date  = $_POST['consignment_date'];
        $consignment_status  = $_POST['consignment_status'];
        
        // Insert into database - UPDATED to include bank_charges field
        $sql = "INSERT INTO Equipment_consignment (
            consignment_number, consignment_origine, consignment_purchase, 
            consignment_shipping, consignment_bank_transfer, consignment_transport, consignment_taxes, 
            consignment_total, consignment_apliedpercent, consignment_document, 
            consignment_date, consignment_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssddddddddss", 
            $consignment_number, $consignment_origine, $consignment_purchase,
            $consignment_shipping, $consignment_bank_transfer, $consignment_transport, $consignment_taxes,
            $consignment_total, $consignment_apliedpercent, $consignment_document,
            $consignment_date, $consignment_status
        );
        
        if($stmt->execute()) {
            $conn->commit();
            header("location:Consignments_List");
            exit();
        } else {
            throw new Exception("Error adding consignment: " . $conn->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }
}

// Generate initial consignment number for display
$preview_sql = "SELECT consignment_id FROM Equipment_consignment ORDER BY consignment_id DESC LIMIT 1";
$preview_result = $conn->query($preview_sql);
if ($preview_result && $preview_result->num_rows > 0) {
    $preview_row = $preview_result->fetch_assoc();
    $preview_last_id = $preview_row['consignment_id'];
    $preview_new_id = $preview_last_id + 1;
} else {
    $preview_new_id = 1;
}
$currentYear = date('Y');
$preview_consignment_number = "BLIS/CONS/" . $currentYear . "/" . str_pad($preview_new_id, 5, '0', STR_PAD_LEFT);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Consignment Details</title>
    <script>
        function calculateValues() {
            // Get all input values
            const purchase = parseFloat(document.getElementById('consignment_purchase').value) || 0;
            const shipping = parseFloat(document.getElementById('consignment_shipping').value) || 0;
            const bankCharges = parseFloat(document.getElementById('consignment_bank_transfer').value) || 0;
            const transport = parseFloat(document.getElementById('consignment_transport').value) || 0;
            const taxes = parseFloat(document.getElementById('consignment_taxes').value) || 0;
              
            
            // Calculate total
            const extraCost = shipping + bankCharges + transport + taxes;
            const total = purchase + extraCost;
            
            // Calculate applied percentage - INCLUDING bank charges
            let percentage = 1; // Default value
            if (total > 0) {
                percentage = (extraCost/purchase) + 1;
            }
            
            // Update fields
            document.getElementById('consignment_total').value = total.toFixed(2);
            document.getElementById('consignment_apliedpercent').value = percentage.toFixed(4);
        }
        
        // Attach event listeners for live calculation
        document.addEventListener('DOMContentLoaded', function() {
            const calculationFields = [
                'consignment_purchase',
                'consignment_shipping', 
                'consignment_bank_transfer',
                'consignment_transport',
                'consignment_taxes',
                'percentage'
            ];
            
            calculationFields.forEach(fieldId => {
                document.getElementById(fieldId).addEventListener('input', calculateValues);
            });
            
            // Calculate initial values if any fields are pre-filled
            calculateValues();
        });
    </script>
</head>
<body class="h-screen font-sans login bg-cover">

<!--/Header-->
<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->
    
    <!--Main-->
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-6xl">
            <div class="leading-loose">
                <form action="" method="POST" enctype="multipart/form-data" class="max-w-6xl m-4 p-10 bg-white rounded shadow-xl">
        
                    <p class="text-gray-800 font-medium text-center text-lg mb-8">Add New Consignment</p>
                    
                    <div class="flex gap-8">
                        
                        <!-- Column 1 -->
                        <div class="flex-1">
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_number">Consignment Number</label>
                                <input class="px-3 py-1 text-gray-700 bg-gray-200 rounded" id="consignment_number" name="consignment_number" type="text" value="<?php echo $preview_consignment_number; ?>" readonly required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_purchase">Purchase Price</label>
                                <input class="px-3 py-1 text-gray-700 bg-green-200 rounded" id="consignment_purchase" name="consignment_purchase" type="number" step="0.01" min="0" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_transport">Local Transport</label>
                                <input class="px-3 py-1 text-gray-700 bg-red-200 rounded" id="consignment_transport" name="consignment_transport" type="number" step="0.01" min="0" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_total">Total</label>
                                <input class="px-3 py-1 text-gray-700 bg-gray-200 rounded" id="consignment_total" name="consignment_total" type="number" step="0.01" readonly required>
                            </div>
                        </div>
                        
                        <!-- Column 2 -->
                        <div class="flex-1">
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_origine">From</label>
                                <input class="px-3 py-1 text-gray-700 bg-gray-200 rounded" id="consignment_origine" name="consignment_origine" type="text" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_shipping">Shipping Price</label>
                                <input class="px-3 py-1 text-gray-700 bg-red-200 rounded" id="consignment_shipping" name="consignment_shipping" type="number" step="0.01" min="0" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_bank_transfer">Bank Charges</label>
                                <input class="px-3 py-1 text-gray-700 bg-red-200 rounded" id="consignment_bank_transfer" name="consignment_bank_transfer" type="number" step="0.01" min="0" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_taxes">Taxes</label>
                                <input class="px-3 py-1 text-gray-700 bg-red-200 rounded" id="consignment_taxes" name="consignment_taxes" type="number" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <!-- Column 3 -->
                        <div class="flex-1">
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_apliedpercent">Purcase Coefficient</label>
                                <input class="px-3 py-1 text-gray-700 bg-gray-200 rounded" id="consignment_apliedpercent" name="consignment_apliedpercent" type="number" step="0.0001" readonly required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_document">Invoice</label>
                                <input class="px-3 py-1 text-gray-700 bg-gray-200 rounded" id="consignment_document" name="consignment_document" type="file">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_date">Date</label>
                                <input class="px-3 py-1 text-gray-700 bg-gray-200 rounded" id="consignment_date" name="consignment_date" value="<?php echo date("Y-m-d");?>" type="date" readonly>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm text-gray-600 mb-1" for="consignment_status">Status</label>
                                <select name="consignment_status" class="px-3 py-1 bg-gray-200 border border-gray-200 text-gray-700 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="consignment_status" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                    </div>

                    <!-- Updated Help text -->
                    <div class="mt-6 text-center text-sm text-gray-500">
                        <p>Total = Purchase + Shipping + Bank Charges + Transport + Taxes <br> Percentage = ((Shipping + Bank Charges + Transport + Taxes) / Total) + 1</p>
                    </div>

                    <div class="mt-6 text-center">
                        <button type="submit" name="Update" class="px-5 py-2 text-white font-light tracking-wider bg-green-500 rounded">Add Consignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php
// Close database connection
if(isset($conn)) {
    $conn->close();
}
ob_end_flush();