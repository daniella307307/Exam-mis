<?php 
include('header.php');

// Secure SQL queries by using prepared statements
$stmt1 = $conn->prepare("SELECT SUM(invc_amount) AS TOPAY FROM school_invoice WHERE invc_term = ? AND invc_year = ? AND invc_school = ?");
$stmt1->bind_param("sss", $setting_term, $setting_year, $school_ref);
$stmt1->execute();
$find_sumary_ttopay = $stmt1->get_result()->fetch_assoc();

$stmt2 = $conn->prepare("SELECT SUM(spay_amount) AS PAID FROM school_payment_details WHERE spay_school = ? AND spay_term = ? AND spay_year = ?");
$stmt2->bind_param("sss", $school_ref, $setting_term, $setting_year);
$stmt2->execute();
$find_sumary_paid = $stmt2->get_result()->fetch_assoc();

$TOPAY = $find_sumary_ttopay['TOPAY'] ?? 0;
$PAID = $find_sumary_paid['PAID'] ?? 0;
$bal = $TOPAY - $PAID;
?>

<!--/Header-->

<div class="flex flex-1">
    <!--Sidebar-->
    <?php include('payment_reports_sidebar.php');?>
    <!--/Sidebar-->
    <!--Main-->
    <main class="bg-white-300 flex-1 p-3 overflow-hidden">

        <div class="flex flex-col">
            <!-- Stats Row Starts Here -->
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="shadow-lg bg-red-vibrant border-l-8 hover:bg-red-vibrant-dark border-red-vibrant-dark mb-2 p-2 md:w-1/4 mx-2">
                    <div class="p-4 flex flex-col">
                        <a href="#" class="no-underline text-white text-lg">
                            Number of Students
                        </a>
                        <a href="#" class="no-underline text-white text-2xl">
                            <?php echo htmlspecialchars($Available_students ?? 0); ?>  
                            <i class="fa fa-users float-left mx-2"></i>
                        </a>
                    </div>
                </div>

                <div class="shadow bg-info border-l-8 hover:bg-info-dark border-info-dark mb-2 p-2 md:w-1/4 mx-2">
                    <div class="p-4 flex flex-col">
                        <a href="#" class="no-underline text-white text-2xl">
                           Expected Amount
                        </a>
                        <a href="#" class="no-underline text-white text-lg">
                          <?php echo number_format($TOPAY?? 0, 2); ?> FRW
                        </a>
                    </div>
                </div>

                <div class="shadow bg-warning border-l-8 hover:bg-warning-dark border-warning-dark mb-2 p-2 md:w-1/4 mx-2">
                    <div class="p-4 flex flex-col">
                        <a href="#" class="no-underline text-white text-2xl">
                         Paid Amount
                        </a>
                        <a href="#" class="no-underline text-white text-lg">
                            <?php echo number_format($PAID ?? 0, 2); ?> FRW
                        </a>
                    </div>
                </div>

                <div class="shadow bg-success border-l-8 hover:bg-success-dark border-success-dark mb-2 p-2 md:w-1/4 mx-2">
                    <div class="p-4 flex flex-col">
                        <a href="#" class="no-underline text-white text-2xl">
                          Unpaid Balance
                        </a>
                        <a href="#" class="no-underline text-white text-lg">
                           <?php echo number_format($bal ?? 0, 2); ?> FRW
                        </a>
                    </div>
                </div>
            </div>
 
            <!-- 3D Bar Chart Section -->
            <div class="flex flex-1 mx-2 my-4">
                <div class="shadow-lg bg-white rounded-lg w-full p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Payment Summary</h2>
                    <canvas id="paymentBarChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </main>
    <!--/Main-->
</div>
<!--Footer-->
<?php include('footer.php');?>
<!--/footer-->

</div>

</div>

<!-- Add Chart.js library and 3D plugin -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-3d@0.1.0/dist/chartjs-plugin-3d.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the context of the canvas element
    const ctx = document.getElementById('paymentBarChart').getContext('2d');
    
    // Create the 3D bar chart with sample data
    const paymentBarChart = new Chart(ctx, {
        type: 'bar',
        plugins: [ChartDataLabels],
        data: {
            labels: ['Expected Amount', 'Paid Amount', 'Unpaid Balance'],
            datasets: [{
                label: 'Amount in FRW',
                data: [
                    <?php echo $TOPAY; ?>,    // Expected Amount
                    <?php echo $PAID; ?>,    // Paid Amount
                    <?php echo $bal; ?>      // Unpaid Balance
                ],
                backgroundColor: [
                    'rgba(23, 162, 184, 0.7)',  // Info color for Expected
                    'rgba(255, 193, 7, 0.7)',   // Warning color for Paid
                    'rgba(40, 167, 69, 0.7)'     // Success color for Balance
                ],
                borderColor: [
                    'rgba(23, 162, 184, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(40, 167, 69, 1)'
                ],
                borderWidth: 1,
                borderRadius: 5,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toLocaleString('en-US', {
                                style: 'currency',
                                currency: 'FRW',
                                minimumFractionDigits: 2
                            });
                        }
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: function(value) {
                        return 'FRW ' + value.toLocaleString('en-US', {
                            minimumFractionDigits: 2
                        });
                    },
                    color: '#333',
                    font: {
                        weight: 'bold',
                        size: 12
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('en-US') + ' FRW';
                        },
                        stepSize: 1000000
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                bar: {
                    borderWidth: 1,
                    borderSkipped: false,
                    backgroundColor: function(context) {
                        const ctx = context.chart.ctx;
                        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                        if(context.datasetIndex === 0) {
                            gradient.addColorStop(0, 'rgba(23, 162, 184, 0.8)');
                            gradient.addColorStop(1, 'rgba(23, 162, 184, 0.2)');
                        } else if(context.datasetIndex === 1) {
                            gradient.addColorStop(0, 'rgba(255, 193, 7, 0.8)');
                            gradient.addColorStop(1, 'rgba(255, 193, 7, 0.2)');
                        } else {
                            gradient.addColorStop(0, 'rgba(40, 167, 69, 0.8)');
                            gradient.addColorStop(1, 'rgba(40, 167, 69, 0.2)');
                        }
                        return gradient;
                    }
                }
            }
        }
    });
});
</script>

<script src="../../main.js"></script>
</body>
</html>