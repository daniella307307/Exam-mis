<?php include('header.php');
$STATUS = isset($_GET['STATUS']) && $_GET['STATUS'] === 'Inactive' ? 'Inactive' : 'Active';

$rstmt = mysqli_prepare($conn, "SELECT region_id, region_name, region_status FROM regions_table WHERE region_status = ? ORDER BY region_name");
mysqli_stmt_bind_param($rstmt, 's', $STATUS);
mysqli_stmt_execute($rstmt);
$select_regions = mysqli_stmt_get_result($rstmt);
?>
<!--/Header-->

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">
            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">
                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b flex flex-wrap items-center gap-2">
                        <span class="mr-2">Regions</span>
                        <a href="Regions?STATUS=Active"><button class='bg-green-500 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>Active</button></a>
                        <a href="Regions?STATUS=Inactive"><button class='bg-red-500 hover:bg-yellow-800 text-white font-bold py-2 px-4 rounded'>Inactive</button></a>
                        <a href="Add_new_region" class="ml-auto"><button class='bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded'>+ Add New Region</button></a>
                    </div>
                    <div class="p-3">
                        <table class="table-responsive w-full rounded">
                            <thead>
                                <tr>
                                    <th class="border w-1/12 px-4 py-2">ID</th>
                                    <th class="border w-1/2 px-4 py-2">Region Name</th>
                                    <th class="border w-1/10 px-4 py-2">Status</th>
                                    <th class="border w-1/5 px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($r = mysqli_fetch_assoc($select_regions)): ?>
                                <tr>
                                    <td class="border px-4 py-1"><?= (int)$r['region_id'] ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($r['region_name']) ?></td>
                                    <td class="border px-4 py-2">
                                        <?php if ($STATUS === 'Active'): ?>
                                            <i class="fas fa-unlock text-green-500 mx-2"></i>
                                        <?php else: ?>
                                            <i class="fas fa-lock text-red-500 mx-2"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <?php if ($STATUS === 'Active'): ?>
                                            <a href="Update_Regions?CURRENT=<?= $STATUS ?>&STATUS=Inactive&ID=<?= (int)$r['region_id'] ?>"
                                               class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                <i class="fas fa-lock text-red-500 mx-2"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="Update_Regions?CURRENT=<?= $STATUS ?>&STATUS=Active&ID=<?= (int)$r['region_id'] ?>"
                                               class="bg-teal-300 cursor-pointer rounded p-1 mx-1 text-white">
                                                <i class="fas fa-unlock text-green-500 mx-2"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include('footer.php'); ?>
