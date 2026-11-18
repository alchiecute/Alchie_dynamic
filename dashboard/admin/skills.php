<?php
require __DIR__ . '/../includes/admin_boot.php';
$pageTitle = 'Skills';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        db()->prepare('DELETE FROM skills WHERE id = ?')->execute([$id]);
        flash('success', 'Skill deleted.');
    } elseif ($action === 'save') {
        $name = trim((string)($_POST['name'] ?? ''));
        $pct  = filter_var($_POST['percent'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
        $ord  = (int)($_POST['sort_order'] ?? 0);
        if ($name === '' || mb_strlen($name) > 80 || $pct === false) {
            flash('error', 'Enter a name (max 80 chars) and a percentage from 0 to 100.');
        } elseif ($id) {
            db()->prepare('UPDATE skills SET name=?, percent=?, sort_order=? WHERE id=?')->execute([$name, $pct, $ord, $id]);
            flash('success', 'Skill updated.');
        } else {
            db()->prepare('INSERT INTO skills (name, percent, sort_order) VALUES (?,?,?)')->execute([$name, $pct, $ord]);
            flash('success', 'Skill added.');
        }
    }
    redirect('skills.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = db()->prepare('SELECT * FROM skills WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}
$rows = db()->query('SELECT * FROM skills ORDER BY sort_order, id')->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<form method="post" class="panel">
    <h2><?= $edit ? 'Edit skill' : 'Add skill' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="form-grid">
        <div><label for="name">Name</label><input type="text" id="name" name="name" maxlength="80" required value="<?= e($edit['name'] ?? '') ?>"></div>
        <div><label for="percent">Percent (0–100)</label><input type="number" id="percent" name="percent" min="0" max="100" required value="<?= e($edit['percent'] ?? '') ?>"></div>
        <div><label for="sort_order">Order</label><input type="number" id="sort_order" name="sort_order" value="<?= e($edit['sort_order'] ?? 0) ?>"></div>
    </div>
    <p><button class="btn primary" type="submit"><?= $edit ? 'Save changes' : 'Add skill' ?></button>
       <?php if ($edit): ?><a class="btn secondary" href="skills.php">Cancel</a><?php endif; ?></p>
</form>

<div class="panel table-scroll">
    <table class="data">
        <tr><th>Order</th><th>Skill</th><th>%</th><th></th></tr>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['sort_order'] ?></td><td><?= e($r['name']) ?></td><td><?= (int)$r['percent'] ?>%</td>
            <td class="actions">
                <a class="btn-sm" href="skills.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                <form method="post" data-confirm="Delete this skill?">
                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn-sm btn-danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
