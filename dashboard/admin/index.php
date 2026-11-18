<?php
require __DIR__ . '/../includes/admin_boot.php';
$pageTitle = 'Dashboard';

/* ---------------- data ---------------- */
$one = fn(string $sql) => (int)db()->query($sql)->fetchColumn();

$totalMsgs  = $one('SELECT COUNT(*) FROM messages');
$unreadMsgs = $one('SELECT COUNT(*) FROM messages WHERE is_read = 0');
$projCount  = $one('SELECT COUNT(*) FROM projects');
$webCount   = $one("SELECT COUNT(*) FROM projects WHERE category = 'web'");
$skillCount = $one('SELECT COUNT(*) FROM skills');
$certCount  = $one('SELECT COUNT(*) FROM certificates');
$skillAvg   = (int)round((float)db()->query('SELECT AVG(percent) FROM skills')->fetchColumn());

// Messages per month (last 12 months)
$raw = db()->query("SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c FROM messages
    WHERE created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 11 MONTH)
    GROUP BY ym")->fetchAll(PDO::FETCH_KEY_PAIR);
$mLabels = []; $mVals = [];
$d = new DateTime('first day of this month');
$d->modify('-11 months');
for ($i = 0; $i < 12; $i++) {
    $mLabels[] = $d->format('M');
    $mVals[]   = (int)($raw[$d->format('Y-m')] ?? 0);
    $d->modify('+1 month');
}

// Messages per day (last 14 days)
$raw = db()->query("SELECT DATE(created_at) d, COUNT(*) c FROM messages
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY d")->fetchAll(PDO::FETCH_KEY_PAIR);
$dLabels = []; $dVals = [];
$d = new DateTime('today');
$d->modify('-13 days');
for ($i = 0; $i < 14; $i++) {
    $dLabels[] = $d->format('j');
    $dVals[]   = (int)($raw[$d->format('Y-m-d')] ?? 0);
    $d->modify('+1 day');
}

$topProjects = db()->query('SELECT title, category FROM projects ORDER BY sort_order, id DESC LIMIT 4')->fetchAll();
$topSkills   = db()->query('SELECT name, percent FROM skills ORDER BY percent DESC, id LIMIT 4')->fetchAll();
$catCounts   = db()->query('SELECT category, COUNT(*) c FROM projects GROUP BY category')->fetchAll(PDO::FETCH_KEY_PAIR);

/* ---------------- pure-SVG chart helpers (no JavaScript, CSP-safe) ---------------- */
function svg_bars(array $labels, array $vals, string $aria = 'Messages per month'): string
{
    $w = 560; $h = 200; $padB = 26; $padT = 14;
    $n = count($vals);
    $max = max(1, max($vals));
    $avg = array_sum($vals) / max(1, $n);
    $slot = $w / $n; $bw = $slot * 0.52;
    $plotH = $h - $padB - $padT;
    $out = '<svg class="bar-chart" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="' . e($aria) . '">';
    foreach ($vals as $i => $v) {
        $bh = max(6, ($v / $max) * $plotH);
        $x = $i * $slot + ($slot - $bw) / 2;
        $y = $h - $padB - $bh;
        $hl = $v > 0 && $v >= $avg;
        $out .= '<rect x="' . round($x, 1) . '" y="' . round($y, 1) . '" width="' . round($bw, 1) . '" height="' . round($bh, 1)
              . '" rx="3" fill="' . ($hl ? '#a9cfc2' : '#e6eeea') . '"><title>' . e($labels[$i]) . ': ' . (int)$v . '</title></rect>';
        if ($i % 3 === 0 || $hl) {
            $out .= '<text x="' . round($x + $bw / 2, 1) . '" y="' . ($h - 8) . '" text-anchor="middle"'
                  . ($hl ? ' class="hl"' : '') . '>' . e($labels[$i]) . '</text>';
        }
    }
    if ($avg > 0) {
        $ay = $h - $padB - ($avg / $max) * $plotH;
        $out .= '<line x1="0" x2="' . $w . '" y1="' . round($ay, 1) . '" y2="' . round($ay, 1)
              . '" stroke="#f5a623" stroke-width="1.5" stroke-dasharray="5 4"/>';
    }
    return $out . '</svg>';
}

function line_path(array $vals, float $w, float $h, float $pad): string
{
    $n = count($vals);
    $max = max(1, max($vals));
    $pts = [];
    foreach ($vals as $i => $v) {
        $pts[] = [$pad + $i * (($w - 2 * $pad) / max(1, $n - 1)), $h - $pad - ($v / $max) * ($h - 2 * $pad)];
    }
    $d = 'M' . round($pts[0][0], 1) . ' ' . round($pts[0][1], 1);
    for ($i = 1; $i < $n; $i++) {
        $mx = ($pts[$i - 1][0] + $pts[$i][0]) / 2;
        $d .= ' C' . round($mx, 1) . ' ' . round($pts[$i - 1][1], 1) . ' ' . round($mx, 1) . ' '
            . round($pts[$i][1], 1) . ' ' . round($pts[$i][0], 1) . ' ' . round($pts[$i][1], 1);
    }
    return $d;
}

function svg_spark(array $vals, string $color): string
{
    return '<svg class="spark" viewBox="0 0 120 40" preserveAspectRatio="none" aria-hidden="true"><path d="'
        . line_path($vals, 120, 40, 4) . '" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round"/></svg>';
}

function svg_line(array $labels, array $vals): string
{
    $w = 360; $h = 150;
    $out = '<svg class="line-chart" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="Messages per day">';
    $out .= '<path d="' . line_path($vals, $w, $h - 18, 14) . '" fill="none" stroke="#2e7d5f" stroke-width="2.5" stroke-linecap="round"/>';
    $n = count($vals);
    foreach ($labels as $i => $l) {
        if ($i % 3 === 0) {
            $x = 14 + $i * (($w - 28) / max(1, $n - 1));
            $out .= '<text x="' . round($x, 1) . '" y="' . ($h - 4) . '" text-anchor="middle">' . e($l) . '</text>';
        }
    }
    return $out . '</svg>';
}

function svg_donut(int $pct, string $color): string
{
    $pct = max(0, min(100, $pct));
    $r = 34; $c = 2 * M_PI * $r;
    return '<svg viewBox="0 0 90 90" role="img" aria-label="' . $pct . ' percent">'
        . '<circle cx="45" cy="45" r="' . $r . '" fill="none" stroke="#e1ebe7" stroke-width="10"/>'
        . '<circle cx="45" cy="45" r="' . $r . '" fill="none" stroke="' . $color . '" stroke-width="10" stroke-linecap="round"'
        . ' stroke-dasharray="' . round($c * $pct / 100, 1) . ' ' . round($c, 1) . '" transform="rotate(-90 45 45)"/>'
        . '<text x="45" y="50" text-anchor="middle" font-size="16" font-weight="700" fill="' . $color . '">' . $pct . '%</text></svg>';
}

$unreadPct = $totalMsgs ? (int)round($unreadMsgs / $totalMsgs * 100) : 0;
$webPct    = $projCount ? (int)round($webCount / $projCount * 100) : 0;
$last6     = array_slice($mVals, -6);

/* ---------------- visitor data (needs the `visits` table) ---------------- */
$hasVisits = true;
$vTotal = $vUnique = $vToday = $vChange = 0;
$vMonth = array_fill(0, 12, 0);
$vDaily = array_fill(0, 14, 0);
$vTopPage = '—';
try {
    $vTotal  = $one('SELECT COUNT(*) FROM visits');
    $vUnique = $one('SELECT COUNT(DISTINCT visitor_hash) FROM visits');
    $vToday  = $one('SELECT COUNT(*) FROM visits WHERE created_at >= CURDATE()');

    $raw = db()->query("SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c FROM visits
        WHERE created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 11 MONTH) GROUP BY ym")->fetchAll(PDO::FETCH_KEY_PAIR);
    $d = new DateTime('first day of this month'); $d->modify('-11 months');
    for ($i = 0; $i < 12; $i++) { $vMonth[$i] = (int)($raw[$d->format('Y-m')] ?? 0); $d->modify('+1 month'); }

    $raw = db()->query("SELECT DATE(created_at) d, COUNT(*) c FROM visits
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY d")->fetchAll(PDO::FETCH_KEY_PAIR);
    $d = new DateTime('today'); $d->modify('-13 days');
    for ($i = 0; $i < 14; $i++) { $vDaily[$i] = (int)($raw[$d->format('Y-m-d')] ?? 0); $d->modify('+1 day'); }

    // last 7 days vs the 7 days before
    $cmp = db()->query("SELECT
            SUM(created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS cur,
            SUM(created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)) AS prev
        FROM visits WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)")->fetch();
    $cur = (int)($cmp['cur'] ?? 0); $prev = (int)($cmp['prev'] ?? 0);
    $vChange = $prev > 0 ? (int)round(($cur - $prev) / $prev * 100) : ($cur > 0 ? 100 : 0);

    $row = db()->query('SELECT page FROM visits GROUP BY page ORDER BY COUNT(*) DESC LIMIT 1')->fetchColumn();
    if ($row) { $vTopPage = $row === 'index' ? 'Home' : ucfirst((string)$row); }
} catch (Throwable $e) {
    $hasVisits = false;
}
$barVals  = $hasVisits ? $vMonth : $mVals;
$barTitle = $hasVisits ? 'page views per month' : 'messages per month';


require __DIR__ . '/../includes/admin_header.php';
?>
<div class="dash">
    <div class="dash-col">
        <div class="a-card">
            <div class="a-head"><h3>Analytics <small>· <?= e($barTitle) ?></small></h3><span class="a-chip">12 months</span></div>
            <?= svg_bars($mLabels, $barVals, ucfirst($barTitle)) ?>
        </div>

        <div class="a-card">
            <h3>Visitors <small>· public pages</small></h3>
            <?php if (!$hasVisits): ?>
                <p class="a-note">Visitor tracking is not set up yet. Import <code>migration_visits.sql</code> in phpMyAdmin, then open your public site to start counting.</p>
            <?php else: ?>
            <div class="kpi-row">
                <div class="kpi"><b><?= number_format($vTotal) ?></b><span>Page views</span></div>
                <?= svg_spark($vDaily, '#8fb339') ?>
                <div class="kpi" style="text-align:right"><b><?= number_format($vUnique) ?></b><span>Unique visitors</span><br>
                    <span class="kpi-pill<?= $vChange < 0 ? ' down' : '' ?>"><?= $vChange < 0 ? '▼' : '▲' ?> <?= abs($vChange) ?>% this week</span></div>
            </div>
            <div class="kpi-row">
                <div class="kpi"><b><?= number_format($vToday) ?></b><span>Views today</span></div>
                <?= svg_spark($vMonth, '#3a8fd1') ?>
                <div class="kpi" style="text-align:right"><b><?= e($vTopPage) ?></b><span>Most visited page</span></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="a-card">
            <h3>Overview</h3>
            <div class="kpi-row">
                <div class="kpi"><b><?= $totalMsgs ?></b><span>Total messages</span></div>
                <?= svg_spark($last6, '#8fb339') ?>
                <div class="kpi" style="text-align:right"><b><?= $unreadPct ?>%</b><span>Unread</span></div>
            </div>
            <div class="kpi-row">
                <div class="kpi"><b><?= $projCount ?></b><span>Projects</span></div>
                <?= svg_spark($dVals, '#3a8fd1') ?>
                <div class="kpi" style="text-align:right"><b><?= $skillAvg ?>%</b><span>Average skill level</span></div>
            </div>
        </div>

        <div class="a-card">
            <h3>Quick status</h3>
            <div class="donut-row">
                <div class="donut"><?= svg_donut($unreadPct, '#f5a623') ?>
                    <div class="lbl"><b>Unread</b><span><?= $unreadMsgs ?> of <?= $totalMsgs ?> messages</span></div></div>
                <div class="donut"><?= svg_donut($skillAvg, '#8e24aa') ?>
                    <div class="lbl"><b>Skills</b><span><?= $skillCount ?> skills · average</span></div></div>
                <div class="donut"><?= svg_donut($webPct, '#7986cb') ?>
                    <div class="lbl"><b>Web projects</b><span><?= $webCount ?> of <?= $projCount ?> projects</span></div></div>
            </div>
        </div>
    </div>

    <div class="dash-col">
        <div class="a-card">
            <div class="a-head"><h3>Content summary</h3><span class="a-chip">Live</span></div>
            <div class="mini-stats">
                <div><b><?= $projCount ?></b><span>Projects</span></div>
                <div><b><?= $skillCount ?></b><span>Skills</span></div>
                <div><b><?= $certCount ?></b><span>Certificates</span></div>
            </div>
            <h3>Messages <small>· last 14 days</small></h3>
            <?= svg_line($dLabels, $dVals) ?>
        </div>

        <div class="a-card">
            <div class="top-grid">
                <div>
                    <h4>Latest projects</h4>
                    <ol>
                        <?php foreach ($topProjects as $p): ?>
                            <li><em><?= e($p['title']) ?></em></li>
                        <?php endforeach; ?>
                        <?php if (!$topProjects): ?><li>None yet</li><?php endif; ?>
                    </ol>
                </div>
                <div>
                    <h4>Top skills</h4>
                    <ol>
                        <?php foreach ($topSkills as $s): ?>
                            <li><em><?= e($s['name']) ?></em> <?= (int)$s['percent'] ?>%</li>
                        <?php endforeach; ?>
                        <?php if (!$topSkills): ?><li>None yet</li><?php endif; ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
