<?php
require __DIR__ . '/../includes/admin_boot.php';
$pageTitle = 'Resume / Experience';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        db()->prepare('DELETE FROM timeline WHERE id = ?')->execute([$id]);
        flash('success', 'Entry deleted.');
    } elseif ($action === 'save') {
        $label = trim((string)($_POST['label'] ?? ''));
        $title = trim((string)($_POST['title'] ?? ''));
        $desc  = trim((string)($_POST['description'] ?? ''));
        $ord   = (int)($_POST['sort_order'] ?? 0);
        if ($label === '' || $title === '' || $desc === '' || mb_strlen($label) > 40 || mb_strlen($title) > 120 || mb_strlen($desc) > 1000) {
            flash('error', 'Fill in all fields (label ≤ 40, title ≤ 120, description ≤ 1000 characters).');
        } elseif ($id) {
            db()->prepare('UPDATE timeline SET label=?, title=?, description=?, sort_order=? WHERE id=?')->execute([$label, $title, $desc, $ord, $id]);
            flash('success', 'Entry updated.');
        } else {
            db()->prepare('INSERT INTO timeline (label, title, description, sort_order) VALUES (?,?,?,?)')->execute([$label, $title, $desc, $ord]);
            flash('success', 'Entry added.');
        }
    }
    redirect('timeline.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = db()->prepare('SELECT * FROM timeline WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}
$rows = db()->query('SELECT * FROM timeline ORDER BY sort_order, id')->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<form method="post" class="panel">
    <h2><?= $edit ? 'Edit entry' : 'Add entry' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="form-grid">
        <div><label for="label">Label (e.g. Education)</label><input type="text" id="label" name="label" maxlength="40" required value="<?= e($edit['label'] ?? '') ?>"></div>
        <div><label for="title">Title</label><input type="text" id="title" name="title" maxlength="120" required value="<?= e($edit['title'] ?? '') ?>"></div>
        <div class="full"><label for="description">Description</label><textarea id="description" name="description" rows="4" maxlength="1000" required><?= e($edit['description'] ?? '') ?></textarea></div>
        <div><label for="sort_order">Order</label><input type="number" id="sort_order" name="sort_order" value="<?= e($edit['sort_order'] ?? 0) ?>"></div>
    </div>
    <p><button class="btn primary" type="submit"><?= $edit ? 'Save changes' : 'Add entry' ?></button>
       <?php if ($edit): ?><a class="btn secondary" href="timeline.php">Cancel</a><?php endif; ?></p>
</form>

<div class="panel table-scroll">
    <table class="data">
        <tr><th>Order</th><th>Label</th><th>Title</th><th></th></tr>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['sort_order'] ?></td><td><?= e($r['label']) ?></td><td><?= e($r['title']) ?></td>
            <td class="actions">
                <a class="btn-sm" href="timeline.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                <form method="post" data-confirm="Delete this entry?">
                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn-sm btn-danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
