<?php

class SimplePager {
    public $limit;       // Items per page
    public $page;        // Current page
    public $item_count;  // Total number of items
    public $page_count;  // Total pages
    public $result;      // Current page result
    public $count;       // Number of items on this page

    protected $pdo;      // PDO connection

    public function __construct($pdo, $query, $params = [], $limit = 10, $page = 1, $count_query = null) {
        $this->pdo = $pdo;
        $this->limit = ctype_digit((string) $limit) ? max((int) $limit, 1) : 10;
        $this->page = ctype_digit((string) $page) ? max((int) $page, 1) : 1;

        // Use custom count query if provided
        if ($count_query !== null) {
            $stmt = $this->pdo->prepare($count_query);
        } else {
            $count_query = preg_replace('/SELECT\s.+?\sFROM/i', 'SELECT COUNT(*) FROM', $query, 1);
            $stmt = $this->pdo->prepare($count_query);
        }

        $stmt->execute($params);
        $this->item_count = (int) $stmt->fetchColumn();

        $this->page_count = (int) ceil($this->item_count / $this->limit);
        $offset = ($this->page - 1) * $this->limit;

        // Fetch paginated results
        $paged_query = "$query LIMIT $offset, {$this->limit}";
        $stmt = $this->pdo->prepare($paged_query);
        $stmt->execute($params);
        $this->result = $stmt->fetchAll();

        $this->count = count($this->result);
    }

    public function html($href = '', $param = 'page', $attr = '') {
        if ($this->page_count <= 1) return;

        $prev = max($this->page - 1, 1);
        $next = min($this->page + 1, $this->page_count);

        echo "<nav class='pager' $attr>";
        echo "<a href='?$param=1&$href'>First</a>";
        echo "<a href='?$param=$prev&$href'>Previous</a>";

        for ($i = 1; $i <= $this->page_count; $i++) {
            $active = ($i == $this->page) ? 'class=\"active\"' : '';
            echo "<a href='?$param=$i&$href' $active>$i</a>";
        }

        echo "<a href='?$param=$next&$href'>Next</a>";
        echo "<a href='?$param={$this->page_count}&$href'>Last</a>";
        echo "</nav>";
    }
}
