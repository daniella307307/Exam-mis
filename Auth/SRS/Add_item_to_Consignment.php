<?php
ob_start();
include('header.php');
if(isset($_GET['REF'])){
    $REF = $_GET['REF'];
    $find_cons = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM Equipment_consignment WHERE consignment_id='$REF'"));
    $consignment_apliedpercent = $find_cons['consignment_apliedpercent'];
    $consignment_number = $find_cons['consignment_number'];
}
else{ 
    header('Location:Consignments_List');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Items to Consignment</title>
    <style>
        .autocomplete {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            background-color: white;
        }
        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #d4d4d4;
        }
        .autocomplete-items div:hover {
            background-color: #e9e9e9;
        }
        .autocomplete-active {
            background-color: DodgerBlue !important;
            color: #ffffff;
        }
        .equipment-details {
            font-size: 0.8em;
            color: #666;
            margin-top: 2px;
        }
        .equipment-model {
            font-weight: bold;
            color: #2c5282;
        }
        .equipment-description {
            font-style: italic;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .item-row {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8fafc;
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .item-number {
            font-weight: bold;
            color: #2d3748;
        }
        .remove-item {
            background-color: #e53e3e;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 12px;
        }
        .remove-item:hover {
            background-color: #c53030;
        }
        .add-item-btn {
            background-color: #4299e1;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .add-item-btn:hover {
            background-color: #3182ce;
        }
        .summary-card {
            background-color: #edf2f7;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid #cbd5e0;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .summary-total {
            font-weight: bold;
            font-size: 1.1em;
            border-top: 1px solid #cbd5e0;
            padding-top: 8px;
            margin-top: 8px;
        }
    </style>
</head>
<body class="h-screen font-sans login bg-cover">
<script>
// Global equipment data
var equipmentData = [
    <?php
    $select_equipment = mysqli_query($conn, "SELECT equipments_id, equipments_name, equipments_ModelNo, equipments_description FROM laboratory_equipments");
    $first = true;
    while ($equipment = mysqli_fetch_array($select_equipment)) {
        if (!$first) echo ",";
        echo "{";
        echo "id: '" . $equipment['equipments_id'] . "', ";
        echo "name: '" . addslashes($equipment['equipments_name']) . "', ";
        echo "model: '" . addslashes($equipment['equipments_ModelNo']) . "', ";
        echo "description: '" . addslashes($equipment['equipments_description']) . "'";
        echo "}";
        $first = false;
    }
    ?>
];

// Global item counter
let itemCounter = 1;

function getstate(val) {
    $.ajax({
    type: "POST",
    url: "get_acount.php",
    data:'coutrycode='+val,
    success: function(data){
        $("#statelist").html(data);
    }
    });
}

function getcity(val) {
    $.ajax({
    type: "POST",
    url: "get_city.php",
    data:'statecode='+val,
    success: function(data){
        $("#city").html(data);
    }
    });
}

// Dynamic price calculation for a specific row
function calculatePrices(rowIndex) {
    var unityPrice = parseFloat(document.getElementById('Unity_Price_' + rowIndex).value) || 0;
    var quantity = parseInt(document.getElementById('Quantity_' + rowIndex).value) || 0;
    var appliedPercent = <?php echo $consignment_apliedpercent; ?>;
    
    // Calculate last price
    var lastPrice = unityPrice * appliedPercent;
    document.getElementById('Last_price_' + rowIndex).value = lastPrice.toFixed(2);
    
    // Calculate total price
    var totalPrice = lastPrice * quantity;
    document.getElementById('Total_price_' + rowIndex).value = totalPrice.toFixed(2);
    
    // Update summary
    updateSummary();
}

// Update summary totals
function updateSummary() {
    let totalItems = 0;
    let grandTotal = 0;
    let validItems = 0;
    
    // Calculate totals from all existing rows
    for (let i = 1; i <= itemCounter; i++) {
        const row = document.getElementById('item-row-' + i);
        if (row) {
            const quantity = parseInt(document.getElementById('Quantity_' + i).value) || 0;
            const totalPrice = parseFloat(document.getElementById('Total_price_' + i).value) || 0;
            const equipmentId = document.getElementById('Equipment_id_' + i).value;
            
            // Only count items that have equipment selected
            if (equipmentId && quantity > 0) {
                totalItems += quantity;
                grandTotal += totalPrice;
                validItems++;
            }
        }
    }
    
    // Update summary display
    document.getElementById('summary-total-items').textContent = totalItems;
    document.getElementById('summary-grand-total').textContent = grandTotal.toFixed(2);
    document.getElementById('summary-valid-items').textContent = validItems;
    
    // Update the hidden input for total items (useful for form submission)
    document.getElementById('total-items-count').value = validItems;
}

// Live search functionality with multiple field search
function autocomplete(inp, rowIndex) {
    var currentFocus;
    
    inp.addEventListener("input", function(e) {
        var a, b, i, val = this.value.toLowerCase();
        closeAllLists();
        if (!val) { 
            document.getElementById("Equipment_id_" + rowIndex).value = "";
            updateSummary();
            return false; 
        }
        currentFocus = -1;
        
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        this.parentNode.appendChild(a);
        
        for (i = 0; i < equipmentData.length; i++) {
            // Search in multiple fields
            var nameMatch = equipmentData[i].name.toLowerCase().includes(val);
            var modelMatch = equipmentData[i].model.toLowerCase().includes(val);
            var descriptionMatch = equipmentData[i].description.toLowerCase().includes(val);
            
            if (nameMatch || modelMatch || descriptionMatch) {
                b = document.createElement("DIV");
                
                // Highlight matching parts
                var displayName = equipmentData[i].name;
                var displayModel = equipmentData[i].model;
                var displayDescription = equipmentData[i].description;
                
                // Highlight name if it matches
                if (nameMatch) {
                    var nameIndex = equipmentData[i].name.toLowerCase().indexOf(val);
                    if (nameIndex !== -1) {
                        displayName = equipmentData[i].name.substr(0, nameIndex) + 
                                     "<strong>" + equipmentData[i].name.substr(nameIndex, val.length) + "</strong>" + 
                                     equipmentData[i].name.substr(nameIndex + val.length);
                    }
                }
                
                // Highlight model if it matches
                if (modelMatch) {
                    var modelIndex = equipmentData[i].model.toLowerCase().indexOf(val);
                    if (modelIndex !== -1) {
                        displayModel = equipmentData[i].model.substr(0, modelIndex) + 
                                      "<strong>" + equipmentData[i].model.substr(modelIndex, val.length) + "</strong>" + 
                                      equipmentData[i].model.substr(modelIndex + val.length);
                    }
                }
                
                // Highlight description if it matches
                if (descriptionMatch) {
                    var descIndex = equipmentData[i].description.toLowerCase().indexOf(val);
                    if (descIndex !== -1) {
                        displayDescription = equipmentData[i].description.substr(0, descIndex) + 
                                            "<strong>" + equipmentData[i].description.substr(descIndex, val.length) + "</strong>" + 
                                            equipmentData[i].description.substr(descIndex + val.length);
                    }
                }
                
                b.innerHTML = "<div class='font-medium'>" + displayName + "</div>";
                b.innerHTML += "<div class='equipment-details'>";
                b.innerHTML += "<span class='equipment-model'>Model: " + displayModel + "</span>";
                if (equipmentData[i].description) {
                    b.innerHTML += "<div class='equipment-description'>Description: " + displayDescription + "</div>";
                }
                b.innerHTML += "</div>";
                
                b.innerHTML += "<input type='hidden' value='" + equipmentData[i].id + "'>";
                b.innerHTML += "<input type='hidden' data-name='" + equipmentData[i].name + "'>";
                b.innerHTML += "<input type='hidden' data-model='" + equipmentData[i].model + "'>";
                b.innerHTML += "<input type='hidden' data-description='" + equipmentData[i].description + "'>";
                
                b.addEventListener("click", function(e) {
                    inp.value = this.getElementsByTagName("input")[1].getAttribute("data-name");
                    document.getElementById("Equipment_id_" + rowIndex).value = this.getElementsByTagName("input")[0].value;
                    closeAllLists();
                    // Update summary when equipment is selected
                    updateSummary();
                });
                a.appendChild(b);
            }
        }
        
        // If no results found
        if (a.children.length === 0) {
            b = document.createElement("DIV");
            b.innerHTML = "No equipment found";
            b.style.padding = "10px";
            b.style.color = "#666";
            b.style.fontStyle = "italic";
            a.appendChild(b);
        }
    });
    
    inp.addEventListener("keydown", function(e) {
        var x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.keyCode == 40) {
            currentFocus++;
            addActive(x);
        } else if (e.keyCode == 38) {
            currentFocus--;
            addActive(x);
        } else if (e.keyCode == 13) {
            e.preventDefault();
            if (currentFocus > -1) {
                if (x) x[currentFocus].click();
            }
        }
    });
    
    function addActive(x) {
        if (!x) return false;
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        x[currentFocus].classList.add("autocomplete-active");
    }
    
    function removeActive(x) {
        for (var i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }
    
    function closeAllLists(elmnt) {
        var x = document.getElementsByClassName("autocomplete-items");
        for (var i = 0; i < x.length; i++) {
            if (elmnt != x[i] && elmnt != inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }
    
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
}

// Add new item row
function addNewItem() {
    itemCounter++;
    const itemsContainer = document.getElementById('items-container');
    
    const newItem = document.createElement('div');
    newItem.className = 'item-row';
    newItem.id = 'item-row-' + itemCounter;
    
    newItem.innerHTML = `
        <div class="item-header">
            <span class="item-number">Item #${itemCounter}</span>
            <button type="button" class="remove-item" onclick="removeItem(${itemCounter})">Remove</button>
        </div>
        
        <div class="w-full md:w-2/2 px-3 mb-4">
            <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1">Search Item</label>
            <div class="autocomplete">
                <input id="Equipment_search_${itemCounter}" type="text" placeholder="Type to search for equipment..." class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" autocomplete="off">
                <input type="hidden" id="Equipment_id_${itemCounter}" name="items[${itemCounter}][equipment_id]" required>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600" for="Quantity_${itemCounter}">Quantity</label>
                <input class="w-full px-3 py-1 text-gray-700 bg-gray-200 rounded" id="Quantity_${itemCounter}" name="items[${itemCounter}][quantity]" type="number" min="1" required oninput="calculatePrices(${itemCounter})">
            </div>
            
            <div>
                <label class="block text-sm text-gray-600" for="Unity_Price_${itemCounter}">Unit Price</label>
                <input class="w-full px-3 py-1 text-gray-700 bg-gray-200 rounded" id="Unity_Price_${itemCounter}" name="items[${itemCounter}][unity_price]" type="number" min="0" step="0.01" required oninput="calculatePrices(${itemCounter})">
            </div>
            
            <div>
                <label class="block text-sm text-gray-600" for="Last_price_${itemCounter}">Price After Expenses</label>
                <input class="w-full px-3 py-1 text-gray-700 bg-gray-200 rounded" id="Last_price_${itemCounter}" name="items[${itemCounter}][last_price]" type="number" min="0" step="0.01" readonly>
            </div>
            
            <div>
                <label class="block text-sm text-gray-600" for="Total_price_${itemCounter}">Total Price</label>
                <input class="w-full px-3 py-1 text-gray-700 bg-gray-200 rounded" id="Total_price_${itemCounter}" name="items[${itemCounter}][total_price]" type="number" min="0" step="0.01" readonly>
            </div>
        </div>
    `;
    
    itemsContainer.appendChild(newItem);
    
    // Initialize autocomplete for the new row
    autocomplete(document.getElementById("Equipment_search_" + itemCounter), itemCounter);
    
    // Add event listeners for the new row
    document.getElementById('Unity_Price_' + itemCounter).addEventListener('input', function() { calculatePrices(itemCounter); });
    document.getElementById('Quantity_' + itemCounter).addEventListener('input', function() { calculatePrices(itemCounter); });
}

// Remove item row
function removeItem(rowIndex) {
    if (itemCounter > 1) {
        const itemToRemove = document.getElementById('item-row-' + rowIndex);
        if (itemToRemove) {
            itemToRemove.remove();
            updateSummary();
        }
    } else {
        alert('You must have at least one item.');
    }
}

// Initialize when document is ready
document.addEventListener("DOMContentLoaded", function() {
    // Initialize autocomplete for first row
    autocomplete(document.getElementById("Equipment_search_1"), 1);
    
    // Add event listeners for first row price calculations
    document.getElementById('Unity_Price_1').addEventListener('input', function() { calculatePrices(1); });
    document.getElementById('Quantity_1').addEventListener('input', function() { calculatePrices(1); });
    
    // Initial summary update
    updateSummary();
    
    // Validate form before submission
    document.querySelector('form').addEventListener('submit', function(e) {
        let valid = true;
        let errorMessage = '';
        let hasValidItems = false;
        
        // Check each item row
        for (let i = 1; i <= itemCounter; i++) {
            const row = document.getElementById('item-row-' + i);
            if (row) {
                const equipmentId = document.getElementById('Equipment_id_' + i).value;
                const quantity = document.getElementById('Quantity_' + i).value;
                const unityPrice = document.getElementById('Unity_Price_' + i).value;
                
                if (equipmentId && quantity && unityPrice) {
                    hasValidItems = true;
                }
                
                if (equipmentId && (!quantity || quantity < 1)) {
                    valid = false;
                    errorMessage = 'Please enter valid quantity for all items';
                    document.getElementById('Quantity_' + i).focus();
                    break;
                }
                
                if (equipmentId && (!unityPrice || unityPrice < 0)) {
                    valid = false;
                    errorMessage = 'Please enter valid unit price for all items';
                    document.getElementById('Unity_Price_' + i).focus();
                    break;
                }
            }
        }
        
        if (!hasValidItems) {
            valid = false;
            errorMessage = 'Please add at least one item with equipment selected';
        }
        
        if (!valid) {
            e.preventDefault();
            alert(errorMessage);
        }
    });
});
</script>   

<!--/Header-->
<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('dynamic_side_bar.php'); ?>
    <!--/Sidebar-->
    
    <!--Main-->
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-4xl">
            <div class="leading-loose">
                <form action="" method="POST" class="max-w-full m-4 p-10 bg-white rounded shadow-xl">
                    <p class="text-gray-800 font-medium text-center text-lg mb-6">Add Multiple Items to Consignment: <?php echo $consignment_number; ?></p>
                    
                    <?php
                    if(isset($_POST['Update'])){
                        $successCount = 0;
                        $errorCount = 0;
                        $duplicateCount = 0;
                        
                        if(isset($_POST['items']) && is_array($_POST['items'])) {
                            foreach($_POST['items'] as $item) {
                                $Equipment_id = mysqli_real_escape_string($conn, $item['equipment_id']);   
                                $Unity_Price = mysqli_real_escape_string($conn, $item['unity_price']); 
                                $Last_price = mysqli_real_escape_string($conn, $item['last_price']);  
                                $Total_price = mysqli_real_escape_string($conn, $item['total_price']); 
                                $Quantity = mysqli_real_escape_string($conn, $item['quantity']);
                                 
                                // Only process if equipment is selected and values are valid
                                if (!empty($Equipment_id) && $Quantity > 0 && $Unity_Price >= 0) {
                                    // Check for duplicate item
                                    $select_loc = mysqli_query($conn,"SELECT * FROM consignment_details WHERE consdetail_ref='$REF' AND consdetail_item='$Equipment_id'");
                                    $select_num = mysqli_num_rows($select_loc);
                                    
                                    if($select_num > 0){
                                        $duplicateCount++;
                                    } else { 
                                        $update = mysqli_query($conn,"INSERT INTO consignment_details
                                        (consdetail_id, consdetail_item, consdetail_ref, consdetail_quantity, consdetail_uprice, consdetail_tprice, consdetail_status) VALUES 
                                        (NULL, '$Equipment_id', '$REF', '$Quantity', '$Last_price', '$Total_price', 'Active')");
                                        
                                        if($update){
                                            $successCount++;
                                        } else {
                                            $errorCount++;
                                        }
                                    }
                                }
                            }
                            
                            // Display results
                            if($successCount > 0) {
                                echo '<div class="bg-green-300 mb-4 border border-green-500 text-green-800 px-4 py-3 rounded relative" role="alert">
                                        <strong class="font-bold">Success!</strong>
                                        <span class="block sm:inline">' . $successCount . ' item(s) added successfully!</span>
                                    </div>';
                            }
                            
                            if($duplicateCount > 0) {
                                echo '<div class="bg-yellow-300 mb-4 border border-yellow-500 text-yellow-800 px-4 py-3 rounded relative" role="alert">
                                        <strong class="font-bold">Notice:</strong>
                                        <span class="block sm:inline">' . $duplicateCount . ' item(s) were duplicates and skipped.</span>
                                    </div>';
                            }
                            
                            if($errorCount > 0) {
                                echo '<div class="bg-red-300 mb-4 border border-red-500 text-red-800 px-4 py-3 rounded relative" role="alert">
                                        <strong class="font-bold">Error!</strong>
                                        <span class="block sm:inline">' . $errorCount . ' item(s) failed to add. Please try again!</span>
                                    </div>';
                            }
                        } else {
                            echo '<div class="bg-red-300 mb-4 border border-red-500 text-red-800 px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Error!</strong>
                                    <span class="block sm:inline">No items were submitted.</span>
                                </div>';
                        }
                    }
                    ?>    
                    
                    <div class="mb-6">
                        <button type="button" class="add-item-btn" onclick="addNewItem()">
                            <i class="fas fa-plus text-white mr-2"></i> Add Another Item
                        </button>
                    </div>
                    
                    <div id="items-container">
                        <!-- First item row -->
                        <div class="item-row" id="item-row-1">
                            <div class="item-header">
                                <span class="item-number">Item #1</span>
                            </div>
                            
                            <div class="w-full md:w-2/2 px-3 mb-4">
                                <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1">Search Item (by name, model, or description)</label>
                                <div class="autocomplete">
                                    <input id="Equipment_search_1" type="text" placeholder="Type to search for equipment by name, model number, or description..." class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" autocomplete="off" required>
                                    <input type="hidden" id="Equipment_id_1" name="items[1][equipment_id]" required>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Search by equipment name, model number, or description</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-600" for="Quantity_1">Quantity</label>
                                    <input class="w-full px-3 py-1 text-gray-700 bg-gray-200 rounded" id="Quantity_1" name="items[1][quantity]" type="number" min="1" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm text-gray-600" for="Unity_Price_1">Unit Price on invoice</label>
                                    <input class="w-full px-3 py-1 text-gray-700 bg-gray-200 rounded" id="Unity_Price_1" name="items[1][unity_price]" type="number" min="0" step="0.01" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm text-gray-600" for="Last_price_1">Price after expenses (<?php echo $consignment_apliedpercent; ?>x)</label>
                                    <input class="w-full px-3 py-1 text-gray-700 bg-gray-200 rounded" id="Last_price_1" name="items[1][last_price]" type="number" min="0" step="0.01" readonly>
                                </div>
                                
                                <div>
                                    <label class="block text-sm text-gray-600" for="Total_price_1">Total price</label>
                                    <input class="w-full px-3 py-1 text-gray-700 bg-gray-200 rounded" id="Total_price_1" name="items[1][total_price]" type="number" min="0" step="0.01" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary Card -->
                    <div class="summary-card">
                        <h3 class="font-bold text-lg mb-3 text-center">Order Summary</h3>
                        <div class="space-y-2">
                            <div class="summary-item">
                                <span>Valid Items:</span>
                                <span id="summary-valid-items">0</span>
                            </div>
                            <div class="summary-item">
                                <span>Total Quantity:</span>
                                <span id="summary-total-items">0</span>
                            </div>
                            <div class="summary-item summary-total">
                                <span>Grand Total:</span>
                                <span>$<span id="summary-grand-total">0.00</span></span>
                            </div>
                        </div>
                        <input type="hidden" id="total-items-count" name="total_items_count" value="0">
                    </div>
                      
                    <center>
                        <div class="mt-6 flex gap-4">
                            <button type="submit" name="Update" class="btn px-6 py-2 text-white font-light tracking-wider bg-green-500 rounded hover:bg-green-600">
                                <i class="fas fa-save text-white mr-2"></i> Add All Items
                            </button>
                             
                            <a href="Consignments_details_List?REF=<?php echo $REF; ?>&STATUS=Active" class="btn px-6 py-2 text-white font-light tracking-wider bg-red-500 rounded hover:bg-red-600 no-print">
                                <i class="fas fa-times text-white mr-2"></i> Close & Exit 
                            </a>
                        </div>
                    </center>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>

<?php
ob_end_flush();
?>