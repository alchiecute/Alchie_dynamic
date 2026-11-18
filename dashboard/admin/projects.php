<?php
require __DIR__ . '/../includes/admin_boot.php';
$pageTitle = 'Projects';
$categories = ['web', 'design', 'animation'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $st = db()->prepare('SELECT image FROM projects WHERE id = ?');
        $st->execute([$id]);
        delete_upload($st->fetchColumn() ?: null);
        db()->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
        flash('success', 'Project deleted.');
    } elseif ($action === 'save') {
        $title = trim((string)($_POST['title'] ?? ''));
        $cat   = (string)($_POST['category'] ?? '');
        $desc  = trim((string)($_POST['description'] ?? ''));
        $det   = trim((string)($_POST['details'] ?? ''));
        $tech  = trim((string)($_POST['tech'] ?? ''));
        $ord   = (int)($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($title === '' || mb_strlen($title) > 120) $errors[] = 'Title is required (max 120).';
        if (!in_array($cat, $categories, true))       $errors[] = 'Choose a valid category.';
        if ($desc === '' || mb_strlen($desc) > 1000)  $errors[] = 'Description is required (max 1000).';
        if (mb_strlen($det) > 2000 || mb_strlen($tech) > 200) $errors[] = 'Details or tech list is too long.';

        $newImage = null;
        try { $newImage = handle_image_upload('image', 'projects'); }
        catch (RuntimeException $ex) { $errors[] = $ex->getMessage(); }
        if (!$id && !$newImage && !$errors) $errors[] = 'Please upload a project image.';

        if ($errors) {
            if ($newImage) delete_upload($newImage);
            flash('error', implode(' ', $errors));
        } elseif ($id) {
            $old = db()->prepare('SELECT image FROM projects WHERE id = ?');
            $old->execute([$id]);
            $oldImage = $old->fetchColumn();
            db()->prepare('UPDATE projects SET title=?, category=?, description=?, details=?, tech=?, sort_order=?, image=? WHERE id=?')
                ->execute([$title, $cat, $desc, $det, $tech, $ord, $newImage ?: $oldImage, $id]);
            if ($newImage) delete_upload($oldImage);
            flash('success', 'Project updated.');
        } else {
            db()->prepare('INSERT INTO projects (title, category, description, details, tech, image, sort_order) VALUES (?,?,?,?,?,?,?)')
                ->execute([$title, $cat, $desc, $det, $tech, $newImage, $ord]);
            flash('success', 'Project added.');
        }
    }
    redirect('projects.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = db()->prepare('SELECT * FROM projects WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}
$rows = db()->query('SELECT * FROM projects ORDER BY sort_order, id DESC')->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<form method="post" enctype="multipart/form-data" class="panel">
    <h2><?= $edit ? 'Edit project' : 'Add project' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="form-grid">
        <div><label for="title">Title</label><input type="text" id="title" name="title" maxlength="120" required value="<?= e($edit['title'] ?? '') ?>"></div>
        <div>
            <label for="category">Category</label>
            <select id="category" name="category">
                <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c) ?>"<?= ($edit['category'] ?? 'web') === $c ? ' selected' : '' ?>><?= e(ucfirst($c)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="full"><label for="description">Short description</label><textarea id="description" name="description" rows="3" maxlength="1000" required><?= e($edit['description'] ?? '') ?></textarea></div>
        <div class="full"><label for="details">Details (shown in the pop-up)</label><textarea id="details" name="details" rows="3" maxlength="2000"><?= e($edit['details'] ?? '') ?></textarea></div>
        <div><label for="tech">Tools / tech</label><input type="text" id="tech" name="tech" maxlength="200" value="<?= e($edit['tech'] ?? '') ?>"></div>
        <div><label for="sort_order">Order</label><input type="number" id="sort_order" name="sort_order" value="<?= e($edit['sort_order'] ?? 0) ?>"></div>
        <div class="full">
            <label for="image">Image (JPG/PNG/WEBP, max 2 MB)<?= $edit ? ' — leave empty to keep the current one' : '' ?></label>
            <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp">
        </div>
    </div>
    <p><button class="btn primary" type="submit"><?= $edit ? 'Save changes' : 'Add project' ?></button>
       <?php if ($edit): ?><a class="btn secondary" href="projects.php">Cancel</a><?php endif; ?></p>
</form>

<div class="panel table-scroll">
    <table class="data">
        <tr><th>Image</th><th>Title</th><th>Category</th><th>Order</th><th></th></tr>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><img src="../<?= e($r['image']) ?>" alt=""></td>
            <td><?= e($r['title']) ?></td><td><?= e($r['category']) ?></td><td><?= (int)$r['sort_order'] ?></td>
            <td class="actions">
                <a class="btn-sm" href="projects.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                <form method="post" data-confirm="Delete this project?">
                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn-sm btn-danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
