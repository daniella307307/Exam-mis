<?php
include('header.php');

/* --------- Filters --------- */
$STATUS    = (isset($_GET['STATUS']) && $_GET['STATUS'] === 'Inactive') ? 'Inactive' : 'Active';
$YEAR      = isset($_GET['YEAR'])   ? trim($_GET['YEAR'])   : '';
$SCHOOL    = isset($_GET['SCHOOL']) ? (int)$_GET['SCHOOL']  : 0;

$where  = "promotion_status = ?";
$types  = "s";
$params = [$STATUS];

if ($YEAR !== '' && ctype_digit($YEAR)) {
    $where   .= " AND promotion_year = ?";
    $types   .= "s";
    $params[] = $YEAR;
}
if ($SCHOOL > 0) {
    $where   .= " AND promotion_school = ?";
    $types   .= "i";
    $params[] = $SCHOOL;
}

$sql = "SELECT p.promotion_id, p.promotion_name, p.promotion_year, p.promotion_status,
               p.promotion_certification, p.promotion_school,
               s.school_name, c.certification_name
        FROM students_promotion p
        LEFT JOIN schools s         ON p.promotion_school        = s.school_id
        LEFT JOIN certifications c  ON p.promotion_certification = c.certification_id
        WHERE $where
        ORDER BY p.promotion_year DESC, s.school_name ASC, c.certification_name ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

/* --------- Dropdown data --------- */
$schools_q = mysqli_query($conn, "SELECT school_id, school_name FROM schools WHERE school_status='Active' ORDER BY school_name");
$years_q   = mysqli_query($conn, "SELECT DISTINCT promotion_year FROM students_promotion ORDER BY promotion_year DESC");
?>

<div class="flex flex-1">
    <?php include('dynamic_side_bar.php'); ?>

    <main class="bg-white-500 flex-1 p-3 overflow-hidden">
        <div class="flex flex-col">

            <div class="flex flex-1 flex-col md:flex-row lg:flex-row mx-2">
                <div class="mb-2 border-solid border-gray-300 rounded border shadow-sm w-full">

                    <div class="bg-gray-200 px-2 py-3 border-solid border-gray-200 border-b flex justify-between items-center flex-wrap">
                        <div>
                            <strong>Promotions</strong>
                            &nbsp;
                            <a href="Promotions?STATUS=Active"><button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded">Active</button></a>
                            <a href="Promotions?STATUS=Inactive"><button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded">Inactive</button></a>
                        </div>
                        <div class="flex gap-2">
                            <a href="Add_Promotion"><button class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-3 rounded"><i class="fas fa-plus mr-1"></i>Add Promotion</button></a>
                            <a href="Rollover_Promotions"><button class="bg-purple-600 hover:bg-purple-800 text-white font-bold py-2 px-3 rounded"><i class="fas fa-sync mr-1"></i>Bulk Rollover</button></a>
                        </div>
                    </div>

                    <div class="p-3">
                        <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
                            <input type="hidden" name="STATUS" value="<?php echo htmlspecialchars($STATUS); ?>">
                            <div>
                                <label class="block text-xs text-gray-600">Year</label>
                                <select name="YEAR" class="border rounded px-2 py-1 bg-gray-100">
                                    <option value="">All</option>
                                    <?php while ($y = mysqli_fetch_assoc($years_q)): ?>
                                        <option value="<?php echo $y['promotion_year']; ?>" <?php echo ($YEAR === $y['promotion_year']) ? 'selected' : ''; ?>>
                                            <?php echo $y['promotion_year']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600">School</label>
                                <select name="SCHOOL" class="border rounded px-2 py-1 bg-gray-100">
                                    <option value="0">All schools</option>
                                    <?php while ($s = mysqli_fetch_assoc($schools_q)): ?>
                                        <option value="<?php echo (int)$s['school_id']; ?>" <?php echo ($SCHOOL === (int)$s['school_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['school_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <button class="bg-gray-700 hover:bg-gray-900 text-white py-1 px-3 rounded" type="submit">Filter</button>
                                <a href="Promotions?STATUS=<?php echo $STATUS; ?>" class="ml-2 text-sm text-blue-600 hover:underline">Reset</a>
                            </div>
                        </form>

                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border px-3 py-2 text-sm">ID</th>
                                        <th class="border px-3 py-2 text-sm">Name</th>
                                        <th class="border px-3 py-2 text-sm">School</th>
                                        <th class="border px-3 py-2 text-sm">Certification</th>
                                        <th class="border px-3 py-2 text-sm">Year</th>
                                        <th class="border px-3 py-2 text-sm">Status</th>
                                        <th class="border px-3 py-2 text-sm">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (mysqli_num_rows($result) === 0): ?>
                                    <tr>
                                        <td colspan="7" class="border px-3 py-6 text-center text-gray-500">
                                            No promotions match the selected filters.
                                        </td>
                                    </tr>
                                <?php else: while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="border px-3 py-2 text-sm"><?php echo (int)$row['promotion_id']; ?></td>
                                        <td class="border px-3 py-2 text-sm"><?php echo htmlspecialchars((string)$row['promotion_name']); ?></td>
                                        <td class="border px-3 py-2 text-sm"><?php echo htmlspecialchars((string)$row['school_name']); ?>
                                            <span class="text-xs text-gray-400">#<?php echo (int)$row['promotion_school']; ?></span></td>
                                        <td class="border px-3 py-2 text-sm"><?php echo htmlspecialchars((string)$row['certification_name']); ?></td>
                                        <td class="border px-3 py-2 text-sm text-center"><?php echo htmlspecialchars((string)$row['promotion_year']); ?></td>
                                        <td class="border px-3 py-2 text-sm text-center">
                                            <?php if ($row['promotion_status'] === 'Active'): ?>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Active</span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="border px-3 py-2 text-sm text-center">
                                            <a href="Update_Promotion?ID=<?php echo (int)$row['promotion_id']; ?>"
                                               class="bg-teal-400 hover:bg-teal-600 text-white text-xs py-1 px-2 rounded">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                            <a href="Delete_Promotion?ID=<?php echo (int)$row['promotion_id']; ?>"
                                               class="bg-red-500 hover:bg-red-700 text-white text-xs py-1 px-2 rounded">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>

<footer class="bg-grey-darkest text-white p-2">
    <div class="flex flex-1 mx-auto">&copy; My Design</div>
</footer>
</div>
</div>
<script src="../../main.js"></script>
</body>
</html>
