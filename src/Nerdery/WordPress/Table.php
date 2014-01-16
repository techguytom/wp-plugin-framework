<?php
/**
 * File Promotion.php
 *
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\WordPress;


if (false === class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Participant
 *
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class Table extends \WP_List_Table
{
    /**
     * @var array
     */
    protected $columns = array();

    /**
     * @var array
     */
    protected $hiddenColumns = array();

    /**
     * @var array
     */
    protected $sortableColumns = array();

    /**
     * @var callable
     */
    private $actionCallable;

    /**
     * @var string
     */
    private $topTableNav;

    /**
     * @var string
     */
    private $bottomTableNav;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->items = $data;

        $this->prepare_items();

        return parent::__construct();
    }

    /**
     * Adds an action column to the table
     *
     * @param callable $callable
     */
    public function addActionColumn(callable $callable)
    {
        $this->actionCallable = $callable;
        $this->columns['actions'] = 'Actions';
        $this->prepare_items();
    }

    /**
     * Set extra table nav
     *
     * @param string $markup
     */
    public function setTopTableNav($markup)
    {
        $this->topTableNav = $markup;
    }

    public function setBottomTableNav($markup)
    {
        $this->bottomTableNav = $markup;
    }

    /**
     * Prepare items for the table
     *
     * @return void
     */
    public function prepare_items()
    {
        usort($this->items, array($this, 'sort_data'));
        $this->items = $this->prepare_pagination($this->items);
        $this->_column_headers = array($this->columns, $this->hiddenColumns, $this->sortableColumns);
    }

    /**
     * Setup pagination
     *
     * @param array $data
     *
     * @return array
     */
    private function prepare_pagination(array $data)
    {
        $perPage = $this->get_items_per_page('edit_participants');
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice(
            $data,
            (($currentPage-1)*$perPage),
            $perPage
        );

        return $data;
    }

    /**
     * Get list of sortable columns
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        return $this->sortableColumns;
    }

    /**
     * Default column value display
     *
     * @param array $item
     * @param string $column_name
     *
     * @return string|null
     */
    public function column_default($item, $column_name)
    {
        if (!array_key_exists($column_name, $item)) {
            return null;
        }

        return $item[$column_name];
    }

    /**
     * Allow sorting of data
     *
     * @param string $a
     * @param string $b
     *
     * @return int
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = $this->get_default_sort_column();
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }

    /**
     * Get default sort column
     *
     * @return null|string
     */
    private function get_default_sort_column()
    {
        $sortableColumns = $this->get_sortable_columns();

        if (count($sortableColumns) < 1) {
            return null;
        }

        $columnNames = array_keys($sortableColumns);

        return $columnNames[0];
    }

    /**
     * Formats the actions column output
     *
     * @param array $item
     *
     * @return string
     */
    function column_actions($item)
    {
        $callable = $this->actionCallable;
        return $callable($item);
    }

    /**
     * Extra table navigation markup
     *
     * @param string $which top|bottom
     */
    function extra_tablenav($which)
    {
        if ($which === 'top') {
            echo $this->topTableNav;
        }

        if ($which === 'bottom') {
            echo $this->bottomTableNav;
        }
    }
}
