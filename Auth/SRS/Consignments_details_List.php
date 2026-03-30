<?php 
// Start output buffering to prevent any HTML output
ob_start();
include('header.php');

// Export functionality - MUST be at the top and exit immediately
if(isset($_GET['export']) && isset($_GET['FREF'])) {
    $export_type = $_GET['export'];
    $FREF = $_GET['FREF'];
    $STATUS = isset($_GET['STATUS']) ? $_GET['STATUS'] : 'Active';
    
    if($export_type == 'pdf') {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        exportToPDF($conn, $FREF);
        exit;
    } elseif($export_type == 'excel') {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        exportToExcel($conn, $FREF);
        exit;
    }
}

// Clear the initial buffer since we're not exporting yet
ob_end_clean();

if(isset($_GET['FREF'])){
    $STATUS = $_GET['STATUS'];
    $FREF = $_GET['FREF']; 
 
    $find_cons = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM Equipment_consignment WHERE consignment_id='$FREF'"));
 
    $select_countries = mysqli_query($conn,"SELECT * FROM consignment_details 
        LEFT JOIN laboratory_equipments ON consignment_details.consdetail_item = laboratory_equipments.equipments_id
        LEFT JOIN Equipment_consignment ON consignment_details.consdetail_ref = Equipment_consignment.consignment_id 
        WHERE consdetail_ref='$FREF'");
} else {
    header("Location:Consignments_List");
    exit;
}

// Export to PDF function
function exportToPDF($conn, $FREF) {
    $find_cons = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM Equipment_consignment WHERE consignment_id='$FREF'"));
    $select_countries = mysqli_query($conn,"SELECT * FROM consignment_details 
        LEFT JOIN laboratory_equipments ON consignment_details.consdetail_item = laboratory_equipments.equipments_id
        LEFT JOIN Equipment_consignment ON consignment_details.consdetail_ref = Equipment_consignment.consignment_id 
        WHERE consdetail_ref='$FREF'");
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Consignment Details - ' . htmlspecialchars($find_cons['consignment_number']) . '</title>
        <meta charset="UTF-8">
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                color: #333;
            }
            .header { 
                border-bottom: 2px solid #333; 
                padding-bottom: 15px; 
                margin-bottom: 20px;
            }
            .header h1 { 
                text-align: center; 
                margin: 0; 
                color: #2c5282;
            }
            .consignment-info {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
                border-left: 4px solid #2c5282;
            }
            .consignment-info p {
                margin: 5px 0;
                font-weight: bold;
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 20px 0;
                font-size: 12px;
            }
            th { 
                background-color: #2c5282; 
                color: white; 
                padding: 10px; 
                text-align: left;
                border: 1px solid #1a365d;
            }
            td { 
                padding: 8px 10px; 
                border: 1px solid #ddd;
            }
            tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .total-row { 
                background-color: #e6f3ff; 
                font-weight: bold;
                border-top: 2px solid #2c5282;
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                font-size: 11px;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>CONSIGNMENT ITEMS DETAILS</h1>
        </div>
        
        <div class="consignment-info">
            <p><strong>Consignment Number:</strong> ' . htmlspecialchars($find_cons['consignment_number']) . '</p>
            <p><strong>Date:</strong> ' . htmlspecialchars($find_cons['consignment_date']) . '</p>
            <p><strong>From:</strong> ' . htmlspecialchars($find_cons['consignment_origine']) . '</p>
        </div>
        
        <h3 class="text-center">List of Items Received</h3>
        
        <table>
            <thead>
                <tr>  
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total Price</th>
                </tr>
            </thead>
            <tbody>';
    
    $grand_total = 0;
    while($row = mysqli_fetch_assoc($select_countries)) {
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($row['consdetail_id']) . '</td>
                    <td>' . htmlspecialchars($row['equipments_name']) . '</td>
                    <td>' . htmlspecialchars($row['equipments_description']) . '</td>
                    <td class="text-right">' . number_format($row['consdetail_quantity'], 0) . '</td>
                    <td class="text-right">' . number_format($row['consdetail_uprice'], 2) . '</td>
                    <td class="text-right">' . number_format($row['consdetail_tprice'], 2) . '</td>
                </tr>';
        $grand_total += $row['consdetail_tprice'];
    }
    
    $html .= '
                <tr class="total-row">
                    <td colspan="5" class="text-right"><strong>GRAND TOTAL:</strong></td>
                    <td class="text-right"><strong>' . number_format($grand_total, 2) . '</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="footer">
            Generated on: ' . date('Y-m-d H:i:s') . ' | Consignment: ' . htmlspecialchars($find_cons['consignment_number']) . '
        </div>
    </body>
    </html>';
    
    // Output as HTML for printing/saving as PDF
    header('Content-Type: text/html');
    header('Content-Disposition: inline; filename="consignment_' . $find_cons['consignment_number'] . '_' . date('Y-m-d') . '.html"');
    echo $html;
    exit;
}

// Export to Excel function (CSV format)
function exportToExcel($conn, $FREF) {
    $find_cons = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM Equipment_consignment WHERE consignment_id='$FREF'"));
    $select_countries = mysqli_query($conn,"SELECT * FROM consignment_details 
        LEFT JOIN laboratory_equipments ON consignment_details.consdetail_item = laboratory_equipments.equipments_id
        LEFT JOIN Equipment_consignment ON consignment_details.consdetail_ref = Equipment_consignment.consignment_id 
        WHERE consdetail_ref='$FREF'");
    
    // Set headers for CSV download - MUST be clean without any previous output
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="consignment_' . $find_cons['consignment_number'] . '_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 in Excel
    fputs($output, "\xEF\xBB\xBF");
    
    // Headers
    fputcsv($output, ['CONSIGNMENT ITEMS DETAILS']);
    fputcsv($output, ['Consignment Number:', $find_cons['consignment_number']]);
    fputcsv($output, ['Date:', $find_cons['consignment_date']]);
    fputcsv($output, ['From:', $find_cons['consignment_origine']]);
    fputcsv($output, []); // Empty row
    
    // Column headers
    fputcsv($output, [
        'ID',
        'Item Name', 
        'Description',
        'Quantity',
        'Unit Price',
        'Total Price'
    ]);
    
    // Data rows
    $grand_total = 0;
    while($row = mysqli_fetch_assoc($select_countries)) {
        fputcsv($output, [
            $row['consdetail_id'],
            $row['equipments_name'],
            $row['equipments_description'],
            number_format($row['consdetail_quantity'], 0),
            number_format($row['consdetail_uprice'], 2),
            number_format($row['consdetail_tprice'], 2)
        ]);
        $grand_total += $row['consdetail_tprice'];
    }
    
    // Total row
    fputcsv($output, []); // Empty row
    fputcsv($output, ['', '', '', '', 'GRAND TOTAL:', number_format($grand_total, 2)]);
    
    fclose($output);
    exit;
}
?>

<!-- Rest of your HTML code remains the same -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="keywords" content="tailwind,tailwindcss,tailwind css,css,starter template,free template,admin templates,admin template,admin dashboard,free tailwind templates,tailwind example">
    <!-- Css -->
    <link rel="stylesheet" href="../../dist/styles.css">
    <link rel="stylesheet" href="../../dist/all.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400i,600,600i,700,700i" rel="stylesheet">
    <title>Consignment Details | BLIS LMS</title>
    
    <style>
    @media print {
        .no-print, header, footer, .sidebar, .bg-gray-200, .flex.justify-between, .bg-teal-300 {
            display: none !important;
        }
        
        .print-only {
            display: block !important;
        }
        
        body {
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .mb-2.border-solid {
            border: none !important;
            box-shadow: none !important;
        }
        
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        
        th, td {
            border: 1px solid #000 !important;
            padding: 8px !important;
        }
        
        th {
            background-color: #f0f0f0 !important;
        }
    }
    </style>
</head>

<body>
<!--Container -->
<div class="mx-auto bg-grey-400">
    <!--Screen-->
    <div class="min-h-screen flex flex-col">
        <!--Header Section Starts Here-->
        <header class="bg-nav">
            <div class="flex justify-between">
                <div class="p-1 mx-3 inline-flex items-center">
                    <i class="fas fa-bars pr-2 text-white" onclick="sidebarToggle()"></i>
                    <h1 class="text-white p-2">BLIS LMS | &nbsp School Name: BLIS Makerspace</h1>
                </div>
                <div class="p-1 flex flex-row items-center">
                    <a href="User_profile" class="text-white p-2 mr-2 no-underline hidden md:block lg:block">School Robotic Store</a>
                    <img onclick="profileToggle()" class="inline-block h-8 w-8 rounded-full" src="../profiles/mika_passport.jpg" alt="">
                    <a href="Roles_per_User" class="text-white p-2 no-underline hidden md:block lg:block">MikaYunusu</a>
                </div>
            </div>
        </header>

        <div class="flex flex-1">
            <!--Sidebar-->
            <?php include('Robotics_materials_side_bar.php');?>
            <!--/Sidebar-->
            <!--Main-->
            <main class="bg-white-500 flex-1 p-3 overflow-hidden">
                <div class="flex flex-col">
                    <!-- Card Section Starts Here -->
                    <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                        <!-- Export and Action Buttons -->
                        <div class="flex justify-between items-center mb-4 w-full no-print">
                            <div>
                                <a href="Consignments_List?STATUS=Active" class="no-print">
                                    <button class='bg-blue-800 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Back to List</button>
                                </a>
                                 <a href="Add_item_to_Consignment?REF=<?php echo $FREF; ?>&STATUS=Active" class="no-print">
                                    <button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-plus text-white mx-2"></i> Add New</button>
                                </a>
                                <a href="Update_item_to_Consignment.php?REF=<?php echo $FREF; ?>&STATUS=Active" class="no-print">
                                    <button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'><i class="fas fa-plus text-white mx-2"></i> Update</button>
                                </a>
                            </div>
                            <div class="no-print">
                                <a href="Consignments_details_List?FREF=<?php echo $FREF; ?>&STATUS=<?php echo $STATUS; ?>&export=excel" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-2 no-print">
                                    <i class="fas fa-file-excel text-white mx-2"></i>Export Excel
                                </a>
                                <a href="Consignments_details_List?FREF=<?php echo $FREF; ?>&STATUS=<?php echo $STATUS; ?>&export=pdf" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-2 no-print">
                                    <i class="fas fa-file-pdf text-white mx-2"></i>Export PDF
                                </a>
                                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded ml-2 no-print">
                                    <i class="fas fa-print text-white mx-2"></i>Print
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- /Cards Section Ends Here -->

                    <!-- Grid Form -->
                    <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                        <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                            <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b no-print">
                                <center><h2 class="text-xl font-bold"><strong>Consignment Items Details</strong></h2></center>
                            </div>
                            <div class="p-3">
                                <!-- Consignment Information -->
                                <div class="mb-6 p-4 bg-blue-50 rounded border border-blue-200">
                                    <strong class="text-lg text-blue-800">Consignment Information:</strong>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                        <p><strong>Consignment Number:</strong> <?php echo $find_cons['consignment_number']; ?></p>
                                        <p><strong>Date:</strong> <?php echo $find_cons['consignment_date']; ?></p>
                                        <p><strong>From:</strong> <?php echo $find_cons['consignment_origine']; ?></p>
                                        <p><strong>Status:</strong> <?php echo $find_cons['consignment_status']; ?></p>
                                    </div>
                                </div>
                                
                                <center><strong class="text-lg"><p>List of Items Received on (<?php echo $find_cons['consignment_date']; ?>)</p></strong></center>
                                
                                <table class="table-auto w-full rounded">
                                    <thead>
                                        <tr>  
                                            <th class="border w-1/12 px-4 py-2">ID</th>
                                            <th class="border w-1/8 px-4 py-2">Item Name</th>
                                            <th class="border w-1/8 px-4 py-2">Description</th>
                                            <th class="border w-1/8 px-4 py-2 text-right">Qty</th>
                                            <th class="border w-1/8 px-4 py-2 text-right">U.Price</th>
                                            <th class="border w-1/8 px-4 py-2 text-right">T.Price</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total_amount = 0;
                                        mysqli_data_seek($select_countries, 0); // Reset pointer
                                        while($Category_details = mysqli_fetch_array($select_countries)){
                                            $total_amount += $Category_details['consdetail_tprice'];
                                        ?>  
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo $Category_details['consdetail_id'];?></td>
                                            <td class="border px-4 py-2"><?php echo $Category_details['equipments_name'];?></td>
                                            <td class="border px-4 py-2"><?php echo $Category_details['equipments_description'];?></td>
                                            <td class="border px-4 py-2 text-right"><?php echo number_format($Category_details['consdetail_quantity'], 0);?></td>
                                            <td class="border px-4 py-2 text-right"><?php echo number_format($Category_details['consdetail_uprice'], 2);?></td>
                                            <td class="border px-4 py-2 text-right"><?php echo number_format($Category_details['consdetail_tprice'], 2);?></td> 
                                        </tr>
                                        <?php } ?>
                                        
                                        <!-- Total Row -->
                                        <tr class="bg-blue-50 font-bold">
                                            <td colspan="5" class="border px-4 py-2 text-right">Total Amount:</td>
                                            <td class="border px-4 py-2 text-right"><?php echo number_format($total_amount, 2); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--/Grid Form-->
                </div>
            </main>
            <!--/Main-->
        </div>
        <!--Footer-->
        <?php include('footer.php')?>
        <!--/footer-->
    </div>
</div>

<script src="../../main.js"></script>
</body>
</html>