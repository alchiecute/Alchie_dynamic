<?php
require __DIR__ . '/../includes/admin_boot.php';
$pageTitle = 'Messages';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($id && $action === 'delete') {
        db()->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        flash('success', 'Message deleted.');
    } elseif ($id && $action === 'toggle') {
        db()->prepare('UPDATE messages SET is_read = 1 - is_read WHERE id = ?')->execute([$id]);
    }
    redirect('messages.php');
}

$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    // escape LIKE wildcards, then use a prepared statement
    $like = '%' . addcslashes(mb_substr($q, 0, 100), '%_\\') . '%';
    $st = db()->prepare('SELECT * FROM messages WHERE name LIKE ? OR email LIKE ? OR message LIKE ? ORDER BY id DESC LIMIT 200');
    $st->execute([$like, $like, $like]);
    $rows = $st->fetchAll();
} else {
    $rows = db()->query('SELECT * FROM messages ORDER BY id DESC LIMIT 200')->fetchAll();
}
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="panel table-scroll">
<?php if (!$rows): ?><p><?= $q !== '' ? 'No messages match your search.' : 'No messages yet.' ?></p><?php else: ?>
    <table class="data">
        <tr><th>From</th><th>Message</th><th>Date</th><th></th></tr>
        <?php foreach ($rows as $m): ?>
        <tr class="<?= $m['is_read'] ? '' : 'unread' ?>">
            <td><?= e($m['name']) ?><br><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></td>
            <td><?= nl2br(e($m['message'])) ?></td>
            <td><?= e($m['created_at']) ?></td>
            <td class="actions">
                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                    <button class="btn-sm" type="submit"><?= $m['is_read'] ? 'Mark unread' : 'Mark read' ?></button></form>
                <form method="post" data-confirm="Delete this message?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                    <button class="btn-sm btn-danger" type="submit">Delete</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
