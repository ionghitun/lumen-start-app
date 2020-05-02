<?php

/**
 * Class ExampleTest
 */
class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function testExample()
    {
        $this->get('/');

        $this->assertEquals(
            json_encode([
                'isError' => false,
                'result'  => [
                    'cors' => 'enabled',
                    'user' => [
                        'register'    => 'enabled',
                        'account'     => [
                            'needActivation'   => true,
                            'canResetPassword' => true
                        ],
                        'socialLogin' => 'enabled'
                    ]
                ]
            ]), $this->response->getContent()
        );
    }
}
