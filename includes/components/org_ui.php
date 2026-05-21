<?php
declare(strict_types=1);

/**
 * UI helpers — semantic Tailwind component class names (no duplicated utilities in PHP).
 */

function org_ui_class(string ...$parts): string
{
    $out = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '') {
            $out[] = $part;
        }
    }

    return htmlspecialchars(implode(' ', array_unique($out)), ENT_QUOTES, 'UTF-8');
}

/**
 * @param array<string, scalar|null> $attrs
 */
function org_ui_attrs(array $attrs): string
{
    $html = [];
    foreach ($attrs as $key => $value) {
        if ($value === null || $value === false) {
            continue;
        }
        $key = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');
        if ($value === true) {
            $html[] = $key;
            continue;
        }
        $html[] = $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
    }

    return implode(' ', $html);
}

/**
 * @param array{
 *   variant?: string,
 *   size?: string,
 *   class?: string,
 *   href?: string,
 *   type?: string,
 *   icon?: string,
 *   id?: string,
 *   attrs?: array<string, scalar|null>
 * } $opts
 */
function org_ui_button(string $label, array $opts = []): string
{
    $variant = $opts['variant'] ?? 'primary';
    $size = $opts['size'] ?? '';
    $extra = trim((string) ($opts['class'] ?? ''));
    $icon = trim((string) ($opts['icon'] ?? ''));
    $href = isset($opts['href']) ? trim((string) $opts['href']) : '';
    $type = $opts['type'] ?? ($href !== '' ? '' : 'button');
    $id = isset($opts['id']) ? trim((string) $opts['id']) : '';

    $classes = ['org-btn'];
    if ($variant !== '') {
        $classes[] = 'org-btn--' . preg_replace('/[^a-z0-9_-]/', '', $variant);
    }
    if ($size === 'sm') {
        $classes[] = 'org-btn--sm';
    }
    if ($extra !== '') {
        $classes[] = $extra;
    }

    $attrs = $opts['attrs'] ?? [];
    if ($id !== '') {
        $attrs['id'] = $id;
    }
    $attrs['class'] = implode(' ', $classes);

    $iconHtml = $icon !== ''
        ? '<i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></i> '
        : '';
    $content = $iconHtml . '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';

    if ($href !== '') {
        $attrs['href'] = $href;

        return '<a ' . org_ui_attrs($attrs) . '>' . $content . '</a>';
    }

    if ($type !== '') {
        $attrs['type'] = $type;
    }

    return '<button ' . org_ui_attrs($attrs) . '>' . $content . '</button>';
}

/**
 * @param array{
 *   title?: string,
 *   body?: string,
 *   class?: string,
 *   variant?: string,
 *   footer?: string
 * } $opts
 */
function org_ui_card_open(array $opts = []): string
{
    $variant = $opts['variant'] ?? '';
    $extra = trim((string) ($opts['class'] ?? ''));
    $classes = ['org-card'];
    if ($variant === 'flat') {
        $classes[] = 'org-card--flat';
    } elseif ($variant === 'interactive') {
        $classes[] = 'org-card--interactive';
    }
    if ($extra !== '') {
        $classes[] = $extra;
    }

    $html = '<article class="' . org_ui_class(...$classes) . '">';
    if (!empty($opts['title'])) {
        $html .= '<h3 class="org-card__title">' . htmlspecialchars((string) $opts['title'], ENT_QUOTES, 'UTF-8') . '</h3>';
    }
    if (!empty($opts['body'])) {
        $html .= '<div class="org-card__body">' . $opts['body'] . '</div>';
    }

    return $html;
}

function org_ui_card_close(): string
{
    return '</article>';
}

function org_components_path(string $file): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $file;
}

function org_component(string $name, array $vars = []): void
{
    $path = org_components_path($name . '.php');
    if (!is_file($path)) {
        return;
    }
    extract($vars, EXTR_SKIP);
    require $path;
}
