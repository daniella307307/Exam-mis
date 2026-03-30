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
    <title>Add Item to Consignment</title>
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
        .hidden {
            display: none;
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
    </style>
</head>
<body class="h-screen font-sans login bg-cover">
<script>
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

// Dynamic price calculation
function calculatePrices() {
    var unityPrice = parseFloat(document.getElementById('Unity_Price').value) || 0;
    var quantity = parseInt(document.getElementById('Quantity').value) || 0;
    var appliedPercent = <?php echo $consignment_apliedpercent; ?>;
    
    // Calculate last price
    var lastPrice = unityPrice * appliedPercent;
    document.getElementById('Last_price').value = lastPrice.toFixed(2);
    
    // Calculate total price
    var totalPrice = lastPrice * quantity;
    document.getElementById('Total_price').value = totalPrice.toFixed(2);
}

// Live search functionality with multiple field search
function autocomplete(inp, arr) {
    var currentFocus;
    
    inp.addEventListener("input", function(e) {
        var a, b, i, val = this.value.toLowerCase();
        closeAllLists();
        if (!val) { 
            document.getElementById("Equipment_id").value = "";
            return false; 
        }
        currentFocus = -1;
        
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        this.parentNode.appendChild(a);
        
        for (i = 0; i < arr.length; i++) {
            // Search in multiple fields
            var nameMatch = arr[i].name.toLowerCase().includes(val);
            var modelMatch = arr[i].model.toLowerCase().includes(val);
            var descriptionMatch = arr[i].description.toLowerCase().includes(val);
            
            if (nameMatch || modelMatch || descriptionMatch) {
                b = document.createElement("DIV");
                
                // Highlight matching parts
                var displayName = arr[i].name;
                var displayModel = arr[i].model;
                var displayDescription = arr[i].description;
                
                // Highlight name if it matches
                if (nameMatch) {
                    var nameIndex = arr[i].name.toLowerCase().indexOf(val);
                    if (nameIndex !== -1) {
                        displayName = arr[i].name.substr(0, nameIndex) + 
                                     "<strong>" + arr[i].name.substr(nameIndex, val.length) + "</strong>" + 
                                     arr[i].name.substr(nameIndex + val.length);
                    }
                }
                
                // Highlight model if it matches
                if (modelMatch) {
                    var modelIndex = arr[i].model.toLowerCase().indexOf(val);
                    if (modelIndex !== -1) {
                        displayModel = arr[i].model.substr(0, modelIndex) + 
                                      "<strong>" + arr[i].model.substr(modelIndex, val.length) + "</strong>" + 
                                      arr[i].model.substr(modelIndex + val.length);
                    }
                }
                
                // Highlight description if it matches
                if (descriptionMatch) {
                    var descIndex = arr[i].description.toLowerCase().indexOf(val);
                    if (descIndex !== -1) {
                        displayDescription = arr[i].description.substr(0, descIndex) + 
                                            "<strong>" + arr[i].description.substr(descIndex, val.length) + "</strong>" + 
                                            arr[i].description.substr(descIndex + val.length);
                    }
                }
                
                b.innerHTML = "<div class='font-medium'>" + displayName + "</div>";
                b.innerHTML += "<div class='equipment-details'>";
                b.innerHTML += "<span class='equipment-model'>Model: " + displayModel + "</span>";
                if (arr[i].description) {
                    b.innerHTML += "<div class='equipment-description'>Description: " + displayDescription + "</div>";
                }
                b.innerHTML += "</div>";
                
                b.innerHTML += "<input type='hidden' value='" + arr[i].id + "'>";
                b.innerHTML += "<input type='hidden' data-name='" + arr[i].name + "'>";
                b.innerHTML += "<input type='hidden' data-model='" + arr[i].model + "'>";
                b.innerHTML += "<input type='hidden' data-description='" + arr[i].description + "'>";
                
                b.addEventListener("click", function(e) {
                    inp.value = this.getElementsByTagName("input")[1].getAttribute("data-name");
                    document.getElementById("Equipment_id").value = this.getElementsByTagName("input")[0].value;
                    
                    // Optional: Display additional equipment info
                    var model = this.getElementsByTagName("input")[2].getAttribute("data-model");
                    var description = this.getElementsByTagName("input")[3].getAttribute("data-description");
                    
                    closeAllLists();
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

// Initialize autocomplete when document is ready
document.addEventListener("DOMContentLoaded", function() {
    // Get equipment data for autocomplete with multiple fields
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
    
    // Initialize autocomplete
    autocomplete(document.getElementById("Equipment_search"), equipmentData);
    
    // Add event listeners for price calculations
    document.getElementById('Unity_Price').addEventListener('input', calculatePrices);
    document.getElementById('Quantity').addEventListener('input', calculatePrices);
    
    // Validate form before submission
    document.querySelector('form').addEventListener('submit', function(e) {
        var equipmentId = document.getElementById('Equipment_id').value;
        if (!equipmentId) {
            e.preventDefault();
            alert('Please select an equipment from the search results');
            document.getElementById('Equipment_search').focus();
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
        <div class="w-full max-w-lg">
            <div class="leading-loose">
                <form action="" method="POST" class="max-w-xl m-4 p-10 bg-white rounded shadow-xl">
                    <p class="text-gray-800 font-medium text-center text-lg mb-4">Add New Item to Consignment: <?php echo $consignment_number; ?></p>
                    
                    <?php
                    if(isset($_POST['Update'])){
                        $Equipment_id = mysqli_real_escape_string($conn, $_POST['Equipment_id']);   
                        $Unity_Price = mysqli_real_escape_string($conn, $_POST['Unity_Price']); 
                        $Last_price = mysqli_real_escape_string($conn, $_POST['Last_price']);  
                        $Total_price = mysqli_real_escape_string($conn, $_POST['Total_price']); 
                        $Quantity = mysqli_real_escape_string($conn, $_POST['Quantity']);
                         
                        // Check for duplicate item
                        $select_loc = mysqli_query($conn,"SELECT * FROM consignment_details WHERE consdetail_ref='$REF' AND consdetail_item='$Equipment_id'");
                        $select_num = mysqli_num_rows($select_loc);
                        
                        if($select_num > 0){
                            echo '<div class="bg-red-300 mb-4 border border-red-500 text-red-800 px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Duplicate Error</strong>
                                    <span class="block sm:inline">This item has already been assigned to Consignment No: '.$consignment_number.'. Try with a different item!</span>
                                </div>';
                        } else { 
                            $update = mysqli_query($conn,"INSERT INTO consignment_details
                            (consdetail_id, consdetail_item, consdetail_ref, consdetail_quantity, consdetail_uprice, consdetail_tprice, consdetail_status) VALUES 
                            (NULL, '$Equipment_id', '$REF', '$Quantity', '$Last_price', '$Total_price', 'Active')");
                            
                            if($update){
                                echo '<div class="bg-green-300 mb-4 border border-green-500 text-green-800 px-4 py-3 rounded relative" role="alert">
                                        <strong class="font-bold">Success!</strong>
                                        <span class="block sm:inline">Item added successfully!</span>
                                    </div>';
                                
                                // Clear form fields after successful submission
                                echo '<script>
                                    document.getElementById("Equipment_search").value = "";
                                    document.getElementById("Equipment_id").value = "";
                                    document.getElementById("Quantity").value = "";
                                    document.getElementById("Unity_Price").value = "";
                                    document.getElementById("Last_price").value = "";
                                    document.getElementById("Total_price").value = "";
                                </script>';
                            } else {
                                echo '<div class="bg-red-300 mb-4 border border-red-500 text-red-800 px-4 py-3 rounded relative" role="alert">
                                        <strong class="font-bold">Error!</strong>
                                        <span class="block sm:inline">Something went wrong. Please try again!</span>
                                    </div>';
                            }
                        }
                    }
                    ?>    
                    
                    <div class="w-full md:w-2/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-light mb-1" for="Equipment_search">
                            Search Item (by name, model, or description)
                        </label>
                        <div class="autocomplete">
                            <input id="Equipment_search" type="text" name="Equipment_search" placeholder="Type to search for equipment by name, model number, or description..." class="block appearance-none w-full bg-grey-200 border border-grey-200 text-grey-darker py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-grey" autocomplete="off" required>
                            <input type="hidden" id="Equipment_id" name="Equipment_id" required>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Search by equipment name, model number, or description</p>
                    </div>
                    
                    <div class="mt-4"> 
                        <label class="block text-sm text-gray-600" for="Quantity">Quantity</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="Quantity" name="Quantity" type="number" min="1" required>
                    </div>
                    
                    <div class="mt-4"> 
                        <label class="block text-sm text-gray-600" for="Unity_Price">Unit Price on invoice</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="Unity_Price" name="Unity_Price" type="number" min="0" step="0.01" required>
                    </div>
                    
                    <div class="mt-4"> 
                        <label class="block text-sm text-gray-600" for="Last_price">Price after expenses (<?php echo $consignment_apliedpercent; ?>x)</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="Last_price" name="Last_price" type="number" min="0" step="0.01" readonly>
                    </div>
                    
                    <div class="mt-4"> 
                        <label class="block text-sm text-gray-600" for="Total_price">Total price</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded" id="Total_price" name="Total_price" type="number" min="0" step="0.01" readonly>
                    </div>
                      
                    <center>
                        <div class="mt-6 flex gap-4">
                            <button type="submit" name="Update" class="btn px-6 py-2 text-white font-light tracking-wider bg-green-500 rounded hover:bg-green-600">
                                <i class="fas fa-plus text-white mr-2"></i> Add Item
                            </button>&nbsp;&nbsp;
                             
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