<?php

/**
 * Data table wrapper — pass headers + rows from parent.
 *
 * @var list<string> $tableHeaders
 * @var list<list<string>> $tableRows
 * @var string $tableCaption
 * @var bool $tableStriped
 * @var string $tableClass
 */

org_tailwind_bootstrap();

$tableHeaders = $tableHeaders ?? [];
$tableRows = $tableRows ?? [];
$tableCaption = trim((string) ($tableCaption ?? ''));
$tableStriped = !empty($tableStriped);
$tableClass = trim((string) ($tableClass ?? ''));
$wrapClass = org_ui_class('org-table-wrap', $tableClass);
$tableClassName = org_ui_class('org-table', $tableStriped ? 'org-table--striped' : '');
?>
<div class="<?php echo $wrapClass; ?>">
    <table class="<?php echo $tableClassName; ?>">
        <?php if ($tableCaption !== ''): ?>
            <caption class="org-sr-only"><?php echo htmlspecialchars($tableCaption, ENT_QUOTES, 'UTF-8'); ?></caption>
        <?php endif; ?>
        <?php if (count($tableHeaders) > 0): ?>
            <thead>
                <tr>
                    <?php foreach ($tableHeaders as $th): ?>
                        <th scope="col"><?php echo htmlspecialchars((string) $th, ENT_QUOTES, 'UTF-8'); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
        <?php endif; ?>
        <tbody>
            <?php foreach ($tableRows as $row): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?php echo $cell; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
