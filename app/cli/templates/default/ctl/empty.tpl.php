<div style="font-size:20px">模板路径: <?= str_replace(PROJECT_REAL_PATH,
        '', $data['path'] ?? '') ?></div>
<div>生成时间: <?= date('Y-m-d H:i:s') ?></div>
<pre><?= '<?= print_r($data ?? [], true) ?>' ?></pre>