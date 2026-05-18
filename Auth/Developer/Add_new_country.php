<?php
ob_start();
include('header.php');

$error = '';

if (isset($_POST['Add_country'])) {
    $Country_name          = trim($_POST['Country_name'] ?? '');
    $Country_currency      = trim($_POST['Country_currency'] ?? '');
    $Country_currency_code = trim($_POST['Country_currency_code'] ?? '');
    $currency_usd          = (float)($_POST['currency_usd'] ?? 0);
    $Country_phonecode     = trim($_POST['Country_phonecode'] ?? '');
    $Country_region        = (int)($_POST['Country_region'] ?? 0);
    $Country_status        = $_POST['Country_status'] ?? 'Active';
    $Country_flag          = trim($_POST['Country_flag'] ?? '');
    if (!in_array($Country_status, ['Active', 'Inactive'], true)) {
        $Country_status = 'Active';
    }

    if ($Country_name === '') {
        $error = 'Country name is required.';
    } else {
        $chk = mysqli_prepare($conn, "SELECT id FROM countries WHERE LOWER(Country_name) = LOWER(?) LIMIT 1");
        mysqli_stmt_bind_param($chk, 's', $Country_name);
        mysqli_stmt_execute($chk);
        $exists = mysqli_stmt_get_result($chk)->fetch_assoc();
        mysqli_stmt_close($chk);

        if ($exists) {
            $error = 'A country with that name already exists (id ' . (int)$exists['id'] . ').';
        } else {
            $ins = mysqli_prepare(
                $conn,
                "INSERT INTO countries
                    (Country_name, Country_currency, Country_currency_code, currency_usd,
                     Country_region, Country_phonecode, Country_status, Country_flag)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $ins, 'sssdisss',
                $Country_name, $Country_currency, $Country_currency_code, $currency_usd,
                $Country_region, $Country_phonecode, $Country_status, $Country_flag
            );
            if (mysqli_stmt_execute($ins)) {
                mysqli_stmt_close($ins);
                header('Location: Countries?STATUS=' . $Country_status);
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
                        <strong>Add New Country</strong> &nbsp; Details
                    </p>

                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-3">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="block text-sm text-gray-600">Country Name</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                               name="Country_name" type="text" required
                               value="<?= htmlspecialchars($_POST['Country_name'] ?? '') ?>"
                               placeholder="e.g. United Kingdom">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="mb-3">
                            <label class="block text-sm text-gray-600">Currency Name</label>
                            <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                                   name="Country_currency" type="text"
                                   value="<?= htmlspecialchars($_POST['Country_currency'] ?? '') ?>"
                                   placeholder="e.g. British Pound">
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm text-gray-600">Currency Code</label>
                            <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                                   name="Country_currency_code" type="text"
                                   value="<?= htmlspecialchars($_POST['Country_currency_code'] ?? '') ?>"
                                   placeholder="e.g. GBP">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="mb-3">
                            <label class="block text-sm text-gray-600">Local to USD rate</label>
                            <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                                   name="currency_usd" type="number" step="0.000001" min="0"
                                   value="<?= htmlspecialchars($_POST['currency_usd'] ?? '0') ?>"
                                   placeholder="e.g. 0.79">
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm text-gray-600">Phone Code</label>
                            <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                                   name="Country_phonecode" type="text" maxlength="4"
                                   value="<?= htmlspecialchars($_POST['Country_phonecode'] ?? '') ?>"
                                   placeholder="e.g. 44">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm text-gray-600">Region</label>
                        <div class="relative">
                            <select name="Country_region"
                                    class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white">
                                <option value="0">-- (none) --</option>
                                <?php
                                $picked_region = (int)($_POST['Country_region'] ?? 0);
                                $rres = mysqli_query($conn, "SELECT region_id, region_name FROM regions_table WHERE region_status = 'Active' ORDER BY region_name");
                                while ($r = mysqli_fetch_assoc($rres)) {
                                    $sel = $picked_region === (int)$r['region_id'] ? 'selected' : '';
                                    echo '<option value="' . (int)$r['region_id'] . '" ' . $sel . '>'
                                         . htmlspecialchars($r['region_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Don't see the right one? <a href="Add_new_region" class="text-blue-600 underline">Add a new region</a> first.
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm text-gray-600">Flag (URL or path, optional)</label>
                        <input class="w-full px-5 py-1 text-gray-700 bg-gray-200 rounded"
                               name="Country_flag" type="text"
                               value="<?= htmlspecialchars($_POST['Country_flag'] ?? '') ?>"
                               placeholder="e.g. flags/uk.png">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-600">Status</label>
                        <div class="relative">
                            <select name="Country_status"
                                    class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white">
                                <?php $ps = $_POST['Country_status'] ?? 'Active'; ?>
                                <option value="Active"   <?= $ps === 'Active'   ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $ps === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-between items-center">
                        <a href="Countries" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to countries</a>
                        <button type="submit" name="Add_country"
                                class="px-4 py-2 text-white font-light tracking-wider bg-green-500 hover:bg-green-600 rounded">
                            Add New Country
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php include('footer.php'); ?>
