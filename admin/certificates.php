<?php
require __DIR__ . '/../includes/admin_boot.php';
$pageTitle = 'Certificates';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $st = db()->prepare('SELECT image FROM certificates WHERE id = ?');
        $st->execute([$id]);
        delete_upload($st->fetchColumn() ?: null);
        db()->prepare('DELETE FROM certificates WHERE id = ?')->execute([$id]);
        flash('success', 'Certificate deleted.');
    } elseif ($action === 'add') {
        $title = trim((string)($_POST['title'] ?? ''));
        try {
            $img = handle_image_upload('image', 'certificates');
            if ($title === '' || mb_strlen($title) > 150) {
                if ($img) delete_upload($img);
                flash('error', 'Title is required (max 150 characters).');
            } elseif (!$img) {
                flash('error', 'Please choose an image.');
            } else {
                db()->prepare('INSERT INTO certificates (title, image) VALUES (?, ?)')->execute([$title, $img]);
                flash('success', 'Certificate added.');
            }
        } catch (RuntimeException $ex) {
            flash('error', $ex->getMessage());
        }
    }
    redirect('certificates.php');
}

$rows = db()->query('SELECT * FROM certificates ORDER BY id DESC')->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<form method="post" enctype="multipart/form-data" class="panel">
    <h2>Add certificate</h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="form-grid">
        <div><label for="title">Title</label><input type="text" id="title" name="title" maxlength="150" required></div>
        <div><label for="image">Image (JPG/PNG/WEBP, max 2 MB)</label><input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp" required></div>
    </div>
    <p><button class="btn primary" type="submit">Add certificate</button></p>
</form>

<div class="panel table-scroll">
    <table class="data">
        <tr><th>Image</th><th>Title</th><th></th></tr>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><img src="../<?= e($r['image']) ?>" alt=""></td><td><?= e($r['title']) ?></td>
            <td class="actions">
                <form method="post" data-confirm="Delete this certificate?">
                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn-sm btn-danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
