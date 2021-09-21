<?php

namespace LandKit\Pagination;

class Pagination
{
    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $pages;

    /**
     * @var int
     */
    private $totalRows;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $range;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var array|string[]
     */
    private $first;

    /**
     * @var array|string[]
     */
    private $last;

    /**
     * @var string
     */
    private $params;

    /**
     * Create new Pagination instance.
     *
     * @param string|null $link
     * @param string|null $title
     * @param array|null $first
     * @param array|null $last
     */
    public function __construct(string $link = null, string $title = null, array $first = null, array $last = null)
    {
        $this->link = $link ?? '?page=';
        $this->title = $title ?? 'Página';
        $this->first = $first ?? ['Primeira página', '&laquo;'];
        $this->last = $last ?? ['Última página', '&raquo;'];
        $this->page = 0;
        $this->pages = 0;
        $this->totalRows = 0;
        $this->limit = 0;
        $this->offset = 0;
    }

    /**
     * @return int
     */
    public function page(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function pages(): int
    {
        return $this->pages;
    }

    /**
     * @return int
     */
    public function totalRows(): int
    {
        return $this->totalRows;
    }

    /**
     * @return int
     */
    public function limit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function offset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $totalRows
     * @param int $limit
     * @param int|null $page
     * @param int $range
     * @param string|null $hash
     * @param array $params
     * @return void
     */
    public function pager(
        int $totalRows,
        int $limit = 10,
        int $page = null,
        int $range = 2,
        string $hash = null,
        array $params = []
    ) {
        $this->totalRows = $this->toPositive($totalRows);
        $this->limit = $this->toPositive($limit);
        $this->range = $this->toPositive($range);
        $this->pages = (int) ceil($this->totalRows / $this->limit);
        $this->page = $page <= $this->pages ? $this->toPositive($page) : $this->pages;

        $this->offset = ($this->page * $this->limit) - $this->limit >= 0 ? ($this->page * $this->limit) - $this->limit : 0;
        $this->hash = !$hash ? null : "#{$hash}";

        $this->addGetParams($params);

        if ($this->totalRows && $this->offset >= $this->totalRows) {
            header('Location: ' . $this->link . ceil($this->totalRows / $this->limit));
            exit;
        }
    }

    /**
     * @param string $parentClass
     * @param bool $fixedFirstAndLastPage
     * @return string
     */
    public function render(string $parentClass = '', bool $fixedFirstAndLastPage = true): string
    {
        if ($this->totalRows > $this->limit):
            $parentClass = $parentClass ?: 'pagination';

            $pager = "<ul class='{$parentClass}'>";
            $pager .= $this->firstPage($fixedFirstAndLastPage);
            $pager .= $this->beforePages();
            $pager .= "<li class='page-item active'><span class='page-link'>{$this->page}</span></li>";
            $pager .= $this->afterPages();
            $pager .= $this->lastPage($fixedFirstAndLastPage);
            $pager .= "</ul>";

            return $pager;
        endif;

        return '';
    }

    /**
     * @param string $start
     * @param string $betweenStartAndEnd
     * @param string $ofTotal
     * @param string $end
     * @return string
     */
    public function info(
        string $start = 'Mostrando',
        string $betweenStartAndEnd = 'a',
        string $ofTotal = 'de',
        string $end = 'registros'
    ): string {
        if ($this->page == 0) {
            return '';
        } elseif ($this->page == 1) {
            $countStart = $this->page;
        } elseif ($this->page == 2) {
            $countStart = $this->limit + 1;
        } else {
            $countStart = ($this->page - 1) * $this->limit + 1;
        }

        $countEnd = ($this->page != $this->pages ? $this->page * $this->limit : $this->totalRows);

        return "{$start} {$countStart} {$betweenStartAndEnd} {$countEnd} {$ofTotal} {$this->totalRows} {$end}";
    }

    /**
     * @return string
     */
    private function beforePages(): string
    {
        $before = '';

        for ($iPag = $this->page - $this->range; $iPag <= $this->page - 1; $iPag++):
            if ($iPag >= 1):
                $before .= "<li class='page-item'><a class='page-link' title='{$this->title} {$iPag}' href='{$this->link}{$iPag}{$this->hash}{$this->params}'>{$iPag}</a></li>";
            endif;
        endfor;

        return $before;
    }

    /**
     * @return string
     */
    private function afterPages(): string
    {
        $after = '';

        for ($dPag = $this->page + 1; $dPag <= $this->page + $this->range; $dPag++):
            if ($dPag <= $this->pages):
                $after .= "<li class='page-item'><a class='page-link' title='{$this->title} {$dPag}' href='{$this->link}{$dPag}{$this->hash}{$this->params}'>{$dPag}</a></li>";
            endif;
        endfor;

        return $after;
    }

    /**
     * @param bool $fixedFirstAndLastPage
     * @return string
     */
    private function firstPage(bool $fixedFirstAndLastPage = true): string
    {
        if ($fixedFirstAndLastPage) {
            if ($this->page == 1) {
                return "<li class='page-item disabled'><a class='page-link' title='{$this->first[0]}' href='#' tabindex='-1' aria-disabled='true'>{$this->first[1]}</a></li>";
            } else {
                return "<li class='page-item'><a class='page-link' title='{$this->first[0]}' href='{$this->link}1{$this->hash}{$this->params}'>{$this->first[1]}</a></li>";
            }
        }

        return '';
    }

    /**
     * @param bool $fixedFirstAndLastPage
     * @return string
     */
    private function lastPage(bool $fixedFirstAndLastPage = true): string
    {
        if ($fixedFirstAndLastPage) {
            if ($this->page == $this->pages) {
                return "<li class='page-item disabled'><a class='page-link' title='{$this->last[0]}' href='#' tabindex='-1' aria-disabled='true'>{$this->last[1]}</a></li>";
            } else {
                return "<li class='page-item'><a class='page-link' title='{$this->last[0]}' href='{$this->link}{$this->pages}{$this->hash}{$this->params}'>{$this->last[1]}</a></li>";
            }
        }

        return '';
    }

    /**
     * @param int $number
     * @return int
     */
    private function toPositive(int $number): int
    {
        return $number >= 1 ? $number : 1;
    }

    /**
     * @param array $params
     * @return void
     */
    private function addGetParams(array $params)
    {
        $this->params = '';

        if (count($params) > 0) {
            if (isset($params['page'])) {
                unset($params['page']);
            }

            $this->params = '&';
            $this->params .= http_build_query($params);
        }
    }
}