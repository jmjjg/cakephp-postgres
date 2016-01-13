<?php
/**
 * Source code for the Postgres.AutovalidateBehavior unit test class.
 *
 */
namespace Postgres\Test\TestCase\Model\Behavior;

use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * The class Postgres.AutovalidateBehaviorTest is responsible for testing the
 * Postgres.AutovalidateBehavior class.
 */
class AutovalidateBehaviorTest extends TestCase
{

    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Postgres.Comments',
    ];

    /**
     * setUp() method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Comments = TableRegistry::get('Comments');
        //TODO: separate class/remove already loaded behaviors
        $this->Comments->addBehavior('PostgresAutovalidate', ['className' => 'Postgres.Autovalidate']);
    }

    // TODO: test exception when connection is not Postgres
    /**
     * Test join with an undefined association.
     *
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Table Users has no defined association Foos
     * @return                   void
     */
//    public function testJoinUndefinedAssociation()
//    {
//        $this->Users->join('Foos');
//    }

    /**
     * Returns the list of rules as arrays for a given field.
     *
     * @param string $field
     * @return array
     */
    protected function getFieldRules($field)
    {
        $result = [];

        foreach ($this->Comments->validator()->field($field)->rules() as $key => $rule) {
            $result[$key] = [
                'rule' => $rule->get('rule'),
                'on' => $rule->get('on'),
                'last' => $rule->get('last'),
                'message' => $rule->get('message'),
                'provider' => $rule->get('provider'),
                'pass' => $rule->get('pass')
            ];
        }

        return $result;
    }

    /**
     * Check the expected rules for the "status" field.
     *
     * @return void
     */
    public function testCommentsStatusRules()
    {
        $result = $this->getFieldRules('status');

        $expected = [
            'inList' => [
                'rule' => 'inList',
                'on' => null,
                'last' => false,
                'message' => sprintf(__d('postgres', 'Validate::inList'), 'awaiting, ham, spam'),
                'provider' => 'default',
                'pass' => [
                    0 => [ 'awaiting', 'ham', 'spam']
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Check the expected rules for the "name" field.
     *
     * @return void
     */
    public function testCommentsNameRules()
    {
        $result = $this->getFieldRules('name');

        $expected = [
            'minLength' => [
                'rule' => 'minLength',
                'on' => null,
                'last' => false,
                'message' => sprintf(__d('postgres', 'Validate::minLength'), 4),
                'provider' => 'default',
                'pass' => [4]
            ],
            'alphaNumeric' => [
                'rule' => 'alphaNumeric',
                'on' => null,
                'last' => false,
                'message' => __d('postgres', 'Validate::alphaNumeric'),
                'provider' => 'default',
                'pass' => []
            ],
        ];

        $this->assertEquals($expected, $result);
    }
}
