<?php
ob_start();
include('header.php');

$error   = '';
$success = '';

// Handle POST
if (isset($_POST['Add_school'])) {
    $school_name        = trim($_POST['school_name'] ?? '');
    $school_abreviation = trim($_POST['school_abreviation'] ?? '');
    $country_ref        = (int)($_POST['country_ref'] ?? 0);
    $school_region      = (int)($_POST['school_region'] ?? 0);
    $school_status      = $_POST['school_status'] ?? 'Active';
    if (!in_array($school_status, ['Active', 'Inactive'], true)) {
        $school_status = 'Active';
    }

    if ($school_name === '' || $school_abreviation === '' || $country_ref <= 0 || $school_region <= 0) {
        $error = 'All fields are required.';
    } else {
        // Duplicate check (name OR abbreviation, case-insensitive)
        $chk = mysqli_prepare(
            $conn,
            "SELECT school_id FROM schools WHERE LOWER(school_name) = LOWER(?) OR LOWER(school_abreviation) = LOWER(?) LIMIT 1"
        );
        mysqli_stmt_bind_param($chk, 'ss', $school_name, $school_abreviation);
        mysqli_stmt_execute($chk);
        $exists = mysqli_stmt_get_result($chk)->fetch_assoc();
        mysqli_stmt_close($chk);

        if ($exists) {
            $error = 'A school with that name or abbreviation already exists.';
        } else {
            $ins = mysqli_prepare(
                $conn,
                "INSERT INTO schools (school_name, school_abreviation, country_ref, school_region, school_status)
                 VALUES (?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($ins, 'ssiis', $school_name, $school_abreviation, $country_ref, $school_region, $school_status);
            if (mysqli_stmt_execute($ins)) {
                mysqli_stmt_close($ins);
                header('Location: Schools');
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
                        <strong>Add New School</strong> &nbsp; Details
                    </p>

                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-3">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="block text-sm text-gray-600">School Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                               name="school_name" type="text" required
                               value="<?= htmlspecialchars($_POST['school_name'] ?? '') ?>"
                               placeholder="e.g. Bright Angels International School">
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm text-gray-600">Abbreviation</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                               name="school_abreviation" type="text" required
                               value="<?= htmlspecialchars($_POST['school_abreviation'] ?? '') ?>"
                               placeholder="e.g. BAIS">
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm text-gray-600">Country</label>
                        <div class="relative">
                            <select name="country_ref" required
                                    class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white">
                                <option value="">-- Select country --</option>
                                <?php
                                $picked_country = (int)($_POST['country_ref'] ?? 0);
                                $cres = mysqli_query($conn, "SELECT id, Country_name FROM countries WHERE Country_status = 'Active' ORDER BY Country_name");
                                while ($c = mysqli_fetch_assoc($cres)) {
                                    $sel = $picked_country === (int)$c['id'] ? 'selected' : '';
                                    echo '<option value="' . (int)$c['id'] . '" ' . $sel . '>'
                                         . htmlspecialchars($c['Country_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm text-gray-600">Region</label>
                        <div class="relative">
                            <select name="school_region" required
                                    class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white">
                                <option value="">-- Select region --</option>
                                <?php
                                $picked_region = (int)($_POST['school_region'] ?? 0);
                                $rres = mysqli_query($conn, "SELECT region_id, region_name FROM regions_table WHERE region_status = 'Active' ORDER BY region_name");
                                while ($r = mysqli_fetch_assoc($rres)) {
                                    $sel = $picked_region === (int)$r['region_id'] ? 'selected' : '';
                                    echo '<option value="' . (int)$r['region_id'] . '" ' . $sel . '>'
                                         . htmlspecialchars($r['region_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-600">Status</label>
                        <div class="relative">
                            <select name="school_status"
                                    class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white">
                                <?php $picked_status = $_POST['school_status'] ?? 'Active'; ?>
                                <option value="Active"   <?= $picked_status === 'Active'   ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $picked_status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-between items-center">
                        <a href="Schools" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to schools</a>
                        <button type="submit" name="Add_school"
                                class="px-4 py-2 text-white font-light tracking-wider bg-green-500 hover:bg-green-600 rounded">
                            Add New School
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php include('footer.php'); ?>
