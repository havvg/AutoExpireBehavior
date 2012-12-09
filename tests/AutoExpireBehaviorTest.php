<?php

class AutoExpireBehaviorTest extends PHPUnit_Framework_TestCase
{
    protected static $initialized = false;

    protected function setUp()
    {
        if (static::$initialized) {
            return;
        }

        static::$initialized = true;

        $builder = new PropelQuickBuilder();

        $config  = $builder->getConfig();
        $config->setBuildProperty('behavior.auto_expire.class', '../src/AutoExpireBehavior');

        $builder->setConfig($config);
        $builder->setSchema($this->getSchema());

        $builder->build();
    }

    protected function getSchema()
    {
        return <<<XML
<database name="default" defaultIdMethod="native">
    <table name="token" phpName="AEBToken">
        <column name="id" type="integer" autoIncrement="true" primaryKey="true" />
        <column name="token" type="varchar" size="255" required="true" primaryString="true" />

        <behavior name="auto_expire" />
    </table>

    <table name="auto_delete_token" phpName="AEBAutoDeleteToken">
        <column name="id" type="integer" autoIncrement="true" primaryKey="true" />
        <column name="token" type="varchar" size="255" required="true" primaryString="true" />

        <behavior name="auto_expire">
            <parameter name="auto_delete" value="true" />
        </behavior>
    </table>

    <table name="not_required_token" phpName="AEBNotRequiredToken">
        <column name="id" type="integer" autoIncrement="true" primaryKey="true" />
        <column name="token" type="varchar" size="255" required="true" primaryString="true" />

        <behavior name="auto_expire">
            <parameter name="required" value="false" />
        </behavior>
    </table>
</database>
XML;
    }

    public function testSetupIsFine()
    {
        $this->assertTrue(class_exists('AEBToken'),
            'The schema has been loaded correctly.');
    }

    /**
     * @depends testSetupIsFine
     */
    public function testHooksHaveBeenAdded()
    {
        $token = new AEBToken();

        $this->assertTrue(method_exists($token, 'preExpire'),
            'The "preExpire" hook has been added.');
        $this->assertTrue(method_exists($token, 'postExpire'),
            'The "postExpire" hook has been added.');
    }

    /**
     * @depends testSetupIsFine
     */
    public function testIsExpiredHasBeenAdded()
    {
        $token = new AEBToken();

        $this->assertTrue(method_exists($token, 'isExpired'),
            'The "isExpired" method has been added.');
    }

    /**
     * @depends testSetupIsFine
     */
    public function testExpirationMethodsHaveBeenAdded()
    {
        $token = new AEBToken();

        $this->assertTrue(method_exists($token, 'expire'),
            'The "expire" method has been added.');
        $this->assertTrue(method_exists($token, 'doExpire'),
            'The "doExpire" method has been added.');
    }

    /**
     * @depends testSetupIsFine
     */
    public function testExpiration()
    {
        $expiresAt = new DateTime('-1 day');
        $row = array(
            42,
            'foo_bar_token',
            $expiresAt->format('Y-m-d H:i:s'),
        );

        $token = $this->getMock('AEBToken', array(
            'preExpire',
            'doExpire',
            'postExpire',
        ));
        $token
            ->expects($this->once())
            ->method('preExpire')
            ->will($this->returnValue(true))
        ;
        $token
            ->expects($this->once())
            ->method('doExpire')
        ;
        $token
            ->expects($this->once())
            ->method('postExpire')
        ;

        $token->hydrate($row);
    }

    /**
     * @depends testSetupIsFine
     */
    public function testExpirationAborted()
    {
        $expiresAt = new DateTime('-1 day');
        $row = array(
            42,
            'foo_bar_token',
            $expiresAt->format('Y-m-d H:i:s'),
        );

        $token = $this->getMock('AEBToken', array(
            'preExpire',
            'doExpire',
            'postExpire',
        ));
        $token
            ->expects($this->once())
            ->method('preExpire')
            ->will($this->returnValue(false))
        ;
        $token
            ->expects($this->never())
            ->method('doExpire')
        ;
        $token
            ->expects($this->never())
            ->method('postExpire')
        ;

        $token->hydrate($row);
    }

    /**
     * @depends testSetupIsFine
     */
    public function testAutoDeleteIsDisabledByDefault()
    {
        $expiresAt = new DateTime('-1 day');

        $row = array(
            42,
            'foo_bar_token',
            $expiresAt->format('Y-m-d H:i:s'),
        );

        $token = new AEBToken();
        $token->hydrate($row);

        $this->assertTrue($token->isExpired(),
            'The fetched token is expired.');
        $this->assertFalse($token->isDeleted(),
            'By default "auto_delete" is disabled.');
    }

    /**
     * @depends testSetupIsFine
     */
    public function testAutoDeleteEnabled()
    {
        $expiresAt = new DateTime('-1 day');

        $row = array(
            42,
            'foo_bar_token',
            $expiresAt->format('Y-m-d H:i:s'),
        );

        $token = new AEBAutoDeleteToken();
        $token->hydrate($row);

        $this->assertTrue($token->isExpired(),
            'The fetched token is expired.');
        $this->assertTrue($token->isDeleted(),
            'The expired token has been deleted automatically.');
    }

    /**
     * @depends testSetupIsFine
     */
    public function testRequiredIsSet()
    {
        $tableMap = new AEBTokenTableMap();
        $column = $tableMap->getColumn('expires_at');

        $this->assertTrue($column->isNotNull(),
            'By default the expiration date is expired.');
    }

    /**
     * @depends testSetupIsFine
     */
    public function testRequiredIsNotSet()
    {
        $tableMap = new AEBNotRequiredTokenTableMap();
        $column = $tableMap->getColumn('expires_at');

        $this->assertFalse($column->isNotNull(),
            'The expires_at column may be optional.');
    }
}
