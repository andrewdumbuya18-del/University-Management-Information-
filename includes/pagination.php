<?php

function pagination_meta(int $total, int $page, int $perPage): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $page), $pages);
    return [
        'total' => $total,
        'page' => $page,
        'pages' => $pages,
        'per_page' => $perPage,
        'offset' => ($page - 1) * $perPage,
    ];
}

function render_pagination(array $meta, array $extra = []): void
{
    if ($meta['pages'] <= 1) {
        return;
    }

    echo '<nav class="pagination" aria-label="Pagination">';
    for ($page = 1; $page <= $meta['pages']; $page++) {
        $params = array_merge($_GET, $extra, ['page' => $page]);
        $href = '?' . http_build_query($params);
        $active = $page === (int) $meta['page'] ? ' active' : '';
        echo '<a class="page-link' . $active . '" href="' . e($href) . '">' . $page . '</a>';
    }
    echo '</nav>';
}
