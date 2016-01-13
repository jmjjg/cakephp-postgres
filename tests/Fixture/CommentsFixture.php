<?php
/**
 * Source file for the CommentsFixture class.
 */
namespace Postgres\Test\Fixture;

use Cake\Datasource\ConnectionInterface;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * The CommentsFixture class will create the fixture for the comments table,
 * adding SQL CHECK constraints to some fields.
 */
class CommentsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 10, 'autoIncrement' => true, 'default' => null, 'null' => false, 'comment' => null, 'precision' => null, 'unsigned' => null],
        'name' => ['type' => 'string', 'length' => 255, 'default' => null, 'null' => false, 'comment' => null, 'precision' => null, 'fixed' => null],
        'status' => ['type' => 'string', 'length' => 8, 'default' => 'ham', 'null' => false, 'comment' => null, 'precision' => null, 'fixed' => null],
        'post_id' => ['type' => 'integer', 'length' => 10, 'default' => null, 'null' => false, 'comment' => null, 'precision' => null, 'unsigned' => null, 'autoIncrement' => null],
        'content' => ['type' => 'text', 'length' => null, 'default' => null, 'null' => false, 'comment' => null, 'precision' => null],
        'created' => ['type' => 'timestamp', 'length' => null, 'default' => null, 'null' => true, 'comment' => null, 'precision' => null],
        'modified' => ['type' => 'timestamp', 'length' => null, 'default' => null, 'null' => true, 'comment' => null, 'precision' => null],
        '_indexes' => [
            'comments_name_idx' => ['type' => 'index', 'columns' => ['name'], 'length' => []],
            'comments_post_id_idx' => ['type' => 'index', 'columns' => ['post_id'], 'length' => []],
            'comments_status_idx' => ['type' => 'index', 'columns' => ['status'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []]
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Add check constraints.
     *
     * @param ConnectionInterface $db
     * @return boolean
     */
    public function create(ConnectionInterface $db)
    {
        $result = parent::create($db);

        $sql = [
            // TODO: create / check functions in database!
            'ALTER TABLE "comments" ADD CONSTRAINT "comments_status_in_list" CHECK(cakephp_validate_in_list("status", ARRAY[\'awaiting\', \'ham\', \'spam\']));',
            'ALTER TABLE "comments" ADD CONSTRAINT "comments_name_min_length" CHECK(cakephp_validate_min_length("name", 4));',
            'ALTER TABLE "comments" ADD CONSTRAINT "comments_name_alpha_numeric" CHECK(cakephp_validate_alpha_numeric("name"));',
        ];

        foreach ($sql as $stmt) {
            $db->query($stmt);
        }

        return $result;
    }

    /**
     * Records
     *
     * @var array
     */
    public $records = [];
}
