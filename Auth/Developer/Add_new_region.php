<?php
ob_start();
include('header.php');

$error = '';

if (isset($_POST['Add_region'])) {
    $region_name   = trim($_POST['region_name'] ?? '');
    $region_status = $_POST['region_status'] ?? 'Active';
    if (!in_array($region_status, ['Active', 'Inactive'], true)) {
        $region_status = 'Active';
    }

    if ($region_name === '') {
        $error = 'Region name is required.';
    } else {
        $chk = mysqli_prepare($conn, "SELECT region_id FROM regions_table WHERE LOWER(region_name) = LOWER(?) LIMIT 1");
        mysqli_stmt_bind_param($chk, 's', $region_name);
        mysqli_stmt_execute($chk);
        $exists = mysqli_stmt_get_result($chk)->fetch_assoc();
        mysqli_stmt_close($chk);

        if ($exists) {
            $error = 'A region with that name already exists (id ' . (int)$exists['region_id'] . ').';
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO regions_table (region_name, region_status) VALUES (?, ?)");
            mysqli_stmt_bind_param($ins, 'ss', $region_name, $region_status);
            if (mysqli_stmt_execute($ins)) {
                mysqli_stmt_close($ins);
                header('Location: Regions?STATUS=' . $region_status);
                exit;
            } else {
                $error = 'Insert failed: ' . mysqli_error($conn);
                mysqli_stmt_close($ins);
            }
        }
    }
}
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="container mx-auto py-6">
            <div class="w-full max-w-lg mx-auto">
                <form action="" method="POST" class="m-4 p-8 bg-white rounded shadow-xl">
                    <p class="text-gray-800 font-medium mb-4">
                        <strong>Add New Region</strong> &nbsp; Details
                    </p>

                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-3">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="block text-sm text-gray-600">Region Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                               name="region_name" type="text" required
                               value="<?= htmlspecialchars($_POST['region_name'] ?? '') ?>"
                               placeholder="e.g. Western Europe">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-600">Status</label>
                        <div class="relative">
                            <select name="region_status"
                                    class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white">
                                <?php $ps = $_POST['region_status'] ?? 'Active'; ?>
                                <option value="Active"   <?= $ps === 'Active'   ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $ps === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-between items-center">
                        <a href="Regions" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to regions</a>
                        <button type="submit" name="Add_region"
                                class="px-4 py-2 text-white font-light tracking-wider bg-green-500 hover:bg-green-600 rounded">
                            Add New Region
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php include('footer.php'); ?>
