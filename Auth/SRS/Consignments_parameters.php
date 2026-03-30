<!DOCTYPE html>
<html lang="en">
<head>

<?php include('header.php');
 
if(isset($_GET['FREF'])){
	 $FREF=$_GET['FREF'];
	 $find_parameters = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM Equipment_consignment WHERE consignment_id='$FREF'")); 
 }
 else{
 ?><script>
 setTimeout(function() {
  window.location.href = "Consignments_List"; // Replace with your desired URL
}, 10); // 5000 milliseconds = 5 seconds
 
 </script><?php
 }
 
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consignment Parameters - Printable Version</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        /* Hide navbar and other elements when printing */
        @media print {
            body * {
                visibility: hidden;
            }
            
            .printable-area, .printable-area * {
                visibility: visible;
            }
            
            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        /* Screen-specific styles */
        @media screen {
            .container {
                max-width: 800px;
                margin: 20px auto;
                background: white;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border-radius: 5px;
            }
            
            .print-button {
                display: block;
                margin: 20px auto;
                padding: 10px 20px;
                background: #4a90e2;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
            }
            
            .print-button:hover {
                background: #3a7bc8;
            }
        }
        
        /* Printable area styles */
        .printable-area {
            width: 100%;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .print-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .print-header p {
            font-size: 14px;
            color: #666;
        }
        
        .consignment-number {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
            color: #2c3e50;
        }
        
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin: 20px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        
        /* Print-specific adjustments */
        @media print {
            @page {
                margin: 0.5in;
            }
            
            body {
                font-size: 12pt;
            }
            
            .print-header h1 {
                font-size: 18pt;
            }
            
            table {
                font-size: 10pt;
            }
            
            th, td {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print button (only visible on screen) -->
        <button class="print-button no-print" onclick="window.print()">Print This Page</button>
        
        <!-- Printable area (visible in both screen and print) -->
        <div class="printable-area">
            <!-- Print header -->
            <div class="print-header">
                <h1>Consignment Parameters Report</h1>
                <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
            
            <!-- Consignment number -->
            <div class="consignment-number">
                Consignment number: <?php echo isset($find_parameters['consignment_number']) ? $find_parameters['consignment_number'] : 'N/A'; ?>
            </div>
            
            <!-- Table container -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Details</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Status</td>
                            <td><?php echo isset($find_parameters['consignment_status']) ? $find_parameters['consignment_status'] : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td><?php echo isset($find_parameters['consignment_date']) ? $find_parameters['consignment_date'] : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Origine (From)</td>
                            <td><?php echo isset($find_parameters['consignment_origine']) ? $find_parameters['consignment_origine'] : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Total Purchase</td>
                            <td><?php echo isset($find_parameters['consignment_purchase']) ? bcdiv($find_parameters['consignment_purchase'], 1, 2) . ' CFA' : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Bank Charges</td>
                            <td><?php echo isset($find_parameters['consignment_bank_transfer']) ? bcdiv($find_parameters['consignment_bank_transfer'], 1, 2) . ' CFA' : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Local Transport</td>
                            <td><?php echo isset($find_parameters['consignment_transport']) ? bcdiv($find_parameters['consignment_transport'], 1, 2) . ' CFA' : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Taxes</td>
                            <td><?php echo isset($find_parameters['consignment_taxes']) ? bcdiv($find_parameters['consignment_taxes'], 1, 2) . ' CFA' : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Total Purchase</td>
                            <td><?php echo isset($find_parameters['consignment_purchase']) ? bcdiv($find_parameters['consignment_purchase'], 1, 2) . ' CFA' : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Total Cost</td>
                            <td><?php echo isset($find_parameters['consignment_total']) ? bcdiv($find_parameters['consignment_total'], 1, 2) . ' CFA' : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <td>Purchase Coefficient</td>
                            <td><?php echo isset($find_parameters['consignment_apliedpercent']) ? bcdiv($find_parameters['consignment_apliedpercent'], 1, 2) : 'N/A'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>This document was generated automatically. For any inquiries, please contact the administration.</p>
            </div>
        </div>
    </div>
</body>
</html>