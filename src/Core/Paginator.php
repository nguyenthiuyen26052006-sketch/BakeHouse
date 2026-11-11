<?php

declare(strict_types=1);

namespace App\Core;

final class Paginator
{
    private readonly int $totalPages;
    private readonly int $currentPage;

    public function __construct(
        private readonly int $totalItems,
        private readonly int $perPage = 9,
        int $currentPage = 1,
        private readonly array $query = [],
        private readonly string $path = ''
    ) {
        $this->totalPages = max(1, (int) ceil($this->totalItems / max(1, $this->perPage)));
        $this->currentPage = min(max(1, $currentPage), $this->totalPages);
    }

    public static function fromRequest(Request $request, int $totalItems, int $perPage = 9): self
    {
        return new self(
            $totalItems,
            $perPage,
            (int) $request->getQuery('page', 1),
            $request->getAllQuery(),
            $request->getPath()
        );
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getFirstItem(): int
    {
        return $this->totalItems === 0 ? 0 : $this->getOffset() + 1;
    }

    public function getLastItem(): int
    {
        return min($this->totalItems, $this->getOffset() + $this->perPage);
    }

    public function buildUrl(int $page): string
    {
        $params = $this->query;
        $params['page'] = $page;

        return $this->path . '?' . http_build_query($params);
    }

    /** Render Bootstrap 5 pagination, preserving all query-string filters. */
    public function render(): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $start = max(1, $this->currentPage - 2);
        $end = min($this->totalPages, $this->currentPage + 2);

        $html = '<nav aria-label="Phân trang"><ul class="pagination bh-pagination justify-content-center">';
        $html .= $this->item('«', $this->currentPage - 1, $this->currentPage === 1);
        if ($start > 1) {
            $html .= $this->item('1', 1) . ($start > 2 ? $this->item('…', 0, true) : '');
        }
        for ($page = $start; $page <= $end; $page++) {
            $html .= $this->item((string) $page, $page, false, $page === $this->currentPage);
        }
        if ($end < $this->totalPages) {
            $html .= ($end < $this->totalPages - 1 ? $this->item('…', 0, true) : '')
                . $this->item((string) $this->totalPages, $this->totalPages);
        }
        $html .= $this->item('»', $this->currentPage + 1, $this->currentPage === $this->totalPages);

        return $html . '</ul></nav>';
    }

    private function item(string $label, int $page, bool $disabled = false, bool $active = false): string
    {
        $class = 'page-item' . ($disabled ? ' disabled' : '') . ($active ? ' active' : '');
        $href = $disabled ? '#' : htmlspecialchars($this->buildUrl($page), ENT_QUOTES, 'UTF-8');

        return sprintf('<li class="%s"><a class="page-link" href="%s">%s</a></li>', $class, $href, $label);
    }
}
